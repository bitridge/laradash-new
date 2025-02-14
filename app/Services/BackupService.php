<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Tasks\Backup\BackupJob;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupService
{
    protected $settings;
    protected $backupPath;

    public function __construct()
    {
        $this->settings = \App\Models\Setting::get('backup', []);
        $this->backupPath = storage_path('app/backups');
    }

    /**
     * Create a new backup
     */
    public function createBackup()
    {
        try {
            Log::info('Starting backup process...');
            
            // Ensure backup directory exists
            if (!File::isDirectory($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true);
            }

            // Generate backup filename
            $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
            $filepath = $this->backupPath . '/' . $filename;

            Log::info('Creating backup file: ' . $filename);

            // Create database backup
            $backupPath = $this->backupDatabase($filepath);

            // Check if this is an automated backup (enabled in settings)
            $isAutomatedBackup = $this->settings['enabled'] ?? false;

            // Upload to remote storage only if this is an automated backup and storage is properly configured
            if ($isAutomatedBackup && !empty($this->settings['storage_type'])) {
                try {
                    $this->uploadToRemoteStorage($backupPath);
                } catch (\Exception $e) {
                    Log::error('Remote storage upload failed: ' . $e->getMessage());
                    // Continue execution even if remote upload fails
                }
            }

            // Clean up old backups if retention is configured
            if (!empty($this->settings['retention_days'])) {
                try {
                    $this->cleanOldBackups();
                } catch (\Exception $e) {
                    Log::error('Cleanup of old backups failed: ' . $e->getMessage());
                    // Continue execution even if cleanup fails
                }
            }

            $fileSize = file_exists($backupPath) ? filesize($backupPath) : 0;
            Log::info('Backup completed', ['file' => $backupPath, 'size' => $fileSize . ' bytes']);

            return [
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => basename($backupPath),
                'size' => $fileSize
            ];
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create database backup
     */
    protected function backupDatabase($filepath)
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        
        Log::info('Starting database backup...');
        
        // Test command execution
        exec('whoami 2>&1', $testOutput, $testReturn);
        Log::info('Test command output:', [
            'output' => $testOutput,
            'return' => $testReturn
        ]);
        
        Log::info('Database config:', [
            'host' => $dbConfig['host'],
            'database' => $dbConfig['database'],
            'username' => $dbConfig['username']
        ]);

        // Create a temporary file for the password
        $tmpFile = tempnam(sys_get_temp_dir(), 'mysql');
        file_put_contents($tmpFile, "[client]\npassword=" . $dbConfig['password']);
        chmod($tmpFile, 0600);
        
        try {
            // Use full path to mysqldump
            $mysqldump = '/opt/homebrew/bin/mysqldump';
            
            if (!file_exists($mysqldump)) {
                throw new \Exception('mysqldump not found at ' . $mysqldump);
            }

            // Build mysqldump command with defaults-extra-file for secure password handling
            $command = sprintf(
                '%s --defaults-extra-file=%s -h %s -u %s %s > %s 2>&1',
                escapeshellarg($mysqldump),
                escapeshellarg($tmpFile),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            Log::info('Executing mysqldump command...');
            
            // For debugging, log the actual command (with password file path redacted)
            Log::info('Command: ' . preg_replace('/--defaults-extra-file=.*?\s/', '--defaults-extra-file=[REDACTED] ', $command));
            
            $output = [];
            $returnVar = 0;
            
            // Execute the command
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('mysqldump command failed with return code: ' . $returnVar);
                Log::error('Command output: ' . implode("\n", $output));
                throw new \Exception('Database backup failed: ' . implode("\n", $output));
            }

            // Check if the backup file was created and has content
            if (!file_exists($filepath)) {
                Log::error('Backup file was not created');
                throw new \Exception('Backup file was not created');
            }

            $fileSize = filesize($filepath);
            Log::info('Backup file created', ['size' => $fileSize . ' bytes']);

            if ($fileSize === 0) {
                Log::error('Backup file is empty');
                throw new \Exception('Backup file is empty');
            }

            // Now let's compress the file
            $gzFilepath = $filepath . '.gz';
            $this->compressFile($filepath, $gzFilepath);
            
            // Remove the uncompressed file
            unlink($filepath);

            return $gzFilepath;
        } finally {
            // Clean up the temporary password file
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Compress a file using gzip
     */
    protected function compressFile($source, $destination)
    {
        $mode = 'wb9';
        $error = false;
        
        if ($fp_out = gzopen($destination, $mode)) {
            if ($fp_in = fopen($source, 'rb')) {
                while (!feof($fp_in)) {
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                }
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        
        if ($error) {
            throw new \Exception('Error compressing backup file');
        }
        
        return true;
    }

    /**
     * Upload backup to remote storage
     */
    protected function uploadToRemoteStorage($filepath)
    {
        switch ($this->settings['storage_type']) {
            case 'ftp':
                return $this->uploadToFtp($filepath);
            case 'google_drive':
                return $this->uploadToGoogleDrive($filepath);
            default:
                throw new \Exception('Invalid storage type');
        }
    }

    /**
     * Upload backup to FTP server
     */
    protected function uploadToFtp($filepath)
    {
        $ftpConfig = [
            'host' => $this->settings['ftp_host'],
            'username' => $this->settings['ftp_username'],
            'password' => $this->settings['ftp_password'],
            'port' => $this->settings['ftp_port'] ?? 21,
            'root' => $this->settings['ftp_path'] ?? '/backups',
            'passive' => true,
            'ssl' => true,
        ];

        $disk = Storage::build([
            'driver' => 'ftp',
            'config' => $ftpConfig,
        ]);

        $filename = basename($filepath);
        $disk->put($filename, file_get_contents($filepath));
    }

    /**
     * Upload backup to Google Drive
     */
    protected function uploadToGoogleDrive($filepath)
    {
        if (empty($this->settings['google_drive_token'])) {
            throw new \Exception('Google Drive not connected');
        }

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken($this->settings['google_drive_token']);

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                // Update stored token
                $this->settings['google_drive_token'] = $client->getAccessToken();
                \App\Models\Setting::set('backup', $this->settings);
            } else {
                throw new \Exception('Google Drive token expired');
            }
        }

        $service = new Google_Service_Drive($client);
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => basename($filepath),
            'parents' => [$this->settings['google_drive_folder']]
        ]);

        $service->files->create($fileMetadata, [
            'data' => file_get_contents($filepath),
            'mimeType' => 'application/gzip',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);
    }

    /**
     * Clean up old backups
     */
    protected function cleanOldBackups()
    {
        $retentionDays = $this->settings['retention_days'] ?? 30;
        $cutoff = Carbon::now()->subDays($retentionDays);

        // Clean local backups
        foreach (File::glob($this->backupPath . '/*.gz') as $file) {
            if (Carbon::createFromTimestamp(File::lastModified($file))->lt($cutoff)) {
                File::delete($file);
            }
        }

        // Clean remote backups if configured
        if ($this->settings['storage_type'] === 'google_drive') {
            $this->cleanGoogleDriveBackups($cutoff);
        } elseif ($this->settings['storage_type'] === 'ftp') {
            $this->cleanFtpBackups($cutoff);
        }
    }

    /**
     * Clean old backups from Google Drive
     */
    protected function cleanGoogleDriveBackups($cutoff)
    {
        if (empty($this->settings['google_drive_token'])) {
            return;
        }

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken($this->settings['google_drive_token']);

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $this->settings['google_drive_token'] = $client->getAccessToken();
                \App\Models\Setting::set('backup', $this->settings);
            } else {
                return;
            }
        }

        $service = new Google_Service_Drive($client);
        $files = $service->files->listFiles([
            'q' => "'" . $this->settings['google_drive_folder'] . "' in parents and mimeType='application/gzip'",
            'fields' => 'files(id, name, createdTime)'
        ]);

        foreach ($files->getFiles() as $file) {
            $createdTime = Carbon::parse($file->getCreatedTime());
            if ($createdTime->lt($cutoff)) {
                $service->files->delete($file->getId());
            }
        }
    }

    /**
     * Clean old backups from FTP server
     */
    protected function cleanFtpBackups($cutoff)
    {
        $ftpConfig = [
            'host' => $this->settings['ftp_host'],
            'username' => $this->settings['ftp_username'],
            'password' => $this->settings['ftp_password'],
            'port' => $this->settings['ftp_port'] ?? 21,
            'root' => $this->settings['ftp_path'] ?? '/backups',
            'passive' => true,
            'ssl' => true,
        ];

        $disk = Storage::build([
            'driver' => 'ftp',
            'config' => $ftpConfig,
        ]);

        foreach ($disk->listContents() as $file) {
            if ($file['type'] === 'file' && Carbon::parse($file['timestamp'])->lt($cutoff)) {
                $disk->delete($file['path']);
            }
        }
    }

    /**
     * Test FTP connection
     */
    public function testFtpConnection($config)
    {
        try {
            $disk = Storage::build([
                'driver' => 'ftp',
                'config' => [
                    'host' => $config['host'],
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'port' => $config['port'] ?? 21,
                    'root' => $config['path'] ?? '/backups',
                    'passive' => true,
                    'ssl' => true,
                ],
            ]);

            // Try to list contents to verify connection
            $disk->listContents();

            return [
                'success' => true,
                'message' => 'FTP connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'FTP connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * List all local backups
     */
    public function listLocalBackups()
    {
        $backups = [];
        $files = File::glob($this->backupPath . '/*.gz');

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => File::size($file),
                'created_at' => Carbon::createFromTimestamp(File::lastModified($file))->toDateTimeString(),
            ];
        }

        // Sort by created_at in descending order
        usort($backups, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        return [
            'success' => true,
            'backups' => $backups
        ];
    }

    /**
     * Get the full path of a backup file
     */
    public function getBackupPath($filename)
    {
        // Ensure the filename is safe
        $filename = basename($filename);
        return $this->backupPath . '/' . $filename;
    }

    /**
     * Delete a local backup file
     */
    public function deleteLocalBackup($filename)
    {
        try {
            $filepath = $this->getBackupPath($filename);
            
            // Check if file exists
            if (!file_exists($filepath)) {
                return [
                    'success' => false,
                    'message' => 'Backup file not found'
                ];
            }

            // Delete the file
            if (File::delete($filepath)) {
                Log::info('Backup file deleted successfully', ['filename' => $filename]);
                return [
                    'success' => true,
                    'message' => 'Backup deleted successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to delete backup file'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete backup: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ];
        }
    }
} 