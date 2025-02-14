<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Create a backup now
     */
    public function createBackup()
    {
        try {
            $result = $this->backupService->createBackup();
            
            if (!$result['success']) {
                return response()->json($result, 500);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        } catch (\Throwable $t) {
            Log::error('Backup creation failed with fatal error: ' . $t->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Backup failed due to a system error. Please check the logs.'
            ], 500);
        }
    }

    /**
     * Test FTP connection
     */
    public function testFtpConnection(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'path' => 'required|string',
        ]);

        try {
            $result = $this->backupService->testFtpConnection($request->all());
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('FTP connection test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'FTP connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Connect to Google Drive
     */
    public function connectGoogleDrive(Request $request)
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('settings.google-drive.callback'));
        $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    /**
     * Handle Google Drive callback
     */
    public function handleGoogleDriveCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.index')
                ->with('error', 'Failed to connect to Google Drive: ' . $request->get('error'));
        }

        try {
            $client = new \Google_Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(route('settings.google-drive.callback'));

            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
            
            if (!empty($token['error'])) {
                throw new \Exception($token['error_description'] ?? $token['error']);
            }

            // Store the token in settings
            $settings = Setting::get('backup', []);
            $settings['google_drive_token'] = $token;
            $settings['google_drive_connected'] = true;
            Setting::set('backup', $settings);

            return redirect()->route('settings.index')
                ->with('success', 'Successfully connected to Google Drive');
        } catch (\Exception $e) {
            Log::error('Google Drive connection failed: ' . $e->getMessage());
            return redirect()->route('settings.index')
                ->with('error', 'Failed to connect to Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Google Drive
     */
    public function disconnectGoogleDrive()
    {
        try {
            $settings = Setting::get('backup', []);
            unset($settings['google_drive_token']);
            $settings['google_drive_connected'] = false;
            Setting::set('backup', $settings);

            return response()->json([
                'success' => true,
                'message' => 'Successfully disconnected from Google Drive'
            ]);
        } catch (\Exception $e) {
            Log::error('Google Drive disconnection failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect from Google Drive: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List available backups
     */
    public function listBackups()
    {
        try {
            $backups = $this->backupService->listLocalBackups();
            return response()->json($backups);
        } catch (\Exception $e) {
            Log::error('Failed to list backups: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to list backups: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a backup file
     */
    public function downloadBackup($filename)
    {
        try {
            $filePath = $this->backupService->getBackupPath($filename);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            return response()->download($filePath, $filename);
        } catch (\Exception $e) {
            Log::error('Failed to download backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to download backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup($filename)
    {
        try {
            $result = $this->backupService->deleteLocalBackup($filename);
            
            if (!$result['success']) {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to delete backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
        }
    }
} 