<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use App\Models\Setting;
use Carbon\Carbon;

class CreateBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new backup if scheduled';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $settings = Setting::get('backup', []);

        // Check if automated backups are enabled
        if (empty($settings['enabled'])) {
            $this->info('Automated backups are disabled.');
            return;
        }

        // Check if it's time to run the backup based on frequency
        $shouldRun = false;
        $now = Carbon::now();
        $scheduledTime = Carbon::parse($settings['time'] ?? '00:00');
        
        // Only run if we're within 5 minutes of the scheduled time
        if ($now->diffInMinutes($scheduledTime) > 5) {
            $this->info('Not scheduled to run at this time.');
            return;
        }

        switch ($settings['frequency'] ?? 'daily') {
            case 'daily':
                $shouldRun = true;
                break;
            case 'weekly':
                // Run on Sundays
                $shouldRun = $now->dayOfWeek === Carbon::SUNDAY;
                break;
            case 'monthly':
                // Run on the first day of the month
                $shouldRun = $now->day === 1;
                break;
        }

        if (!$shouldRun) {
            $this->info('Not scheduled to run today.');
            return;
        }

        // Create the backup
        $this->info('Creating backup...');
        $result = $backupService->createBackup();

        if ($result['success']) {
            $this->info('Backup created successfully: ' . $result['filename']);
        } else {
            $this->error('Backup failed: ' . $result['message']);
        }
    }
} 