<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Application Settings
            [
                'key' => 'app.name',
                'value' => json_encode('LaraDash'),
                'group' => 'app'
            ],
            [
                'key' => 'app.logo',
                'value' => null,
                'group' => 'app'
            ],

            // SMTP Settings
            [
                'key' => 'smtp.sender_name',
                'value' => json_encode('LaraDash'),
                'group' => 'smtp'
            ],
            [
                'key' => 'smtp.encryption',
                'value' => json_encode('tls'),
                'group' => 'smtp'
            ],
            [
                'key' => 'smtp.host',
                'value' => json_encode(''),
                'group' => 'smtp'
            ],
            [
                'key' => 'smtp.port',
                'value' => json_encode('587'),
                'group' => 'smtp'
            ],
            [
                'key' => 'smtp.username',
                'value' => json_encode(''),
                'group' => 'smtp'
            ],
            [
                'key' => 'smtp.password',
                'value' => json_encode(''),
                'group' => 'smtp'
            ],
            [
                'key' => 'smtp.sender_email',
                'value' => json_encode('noreply@example.com'),
                'group' => 'smtp'
            ],

            // Company Information
            [
                'key' => 'company.name',
                'value' => json_encode('Your Company Name'),
                'group' => 'company'
            ],
            [
                'key' => 'company.address',
                'value' => json_encode('Your Company Address'),
                'group' => 'company'
            ],
            [
                'key' => 'company.email',
                'value' => json_encode('contact@example.com'),
                'group' => 'company'
            ],
            [
                'key' => 'company.phone',
                'value' => json_encode('+1234567890'),
                'group' => 'company'
            ],
            [
                'key' => 'company.url',
                'value' => json_encode('https://example.com'),
                'group' => 'company'
            ],
            [
                'key' => 'company.contact_email',
                'value' => json_encode('contact@example.com'),
                'group' => 'company'
            ],

            // Captcha Settings
            [
                'key' => 'captcha.enabled',
                'value' => json_encode(false),
                'group' => 'captcha'
            ],

            // Backup Settings
            [
                'key' => 'backup.enabled',
                'value' => json_encode(false),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.frequency',
                'value' => json_encode('daily'),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.time',
                'value' => json_encode('00:00'),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.storage_type',
                'value' => json_encode(''),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.ftp_host',
                'value' => json_encode(''),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.ftp_username',
                'value' => json_encode(''),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.ftp_password',
                'value' => json_encode(''),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.ftp_path',
                'value' => json_encode('/backups'),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.google_drive_connected',
                'value' => json_encode(false),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.google_drive_folder',
                'value' => json_encode('/backups'),
                'group' => 'backup'
            ],
            [
                'key' => 'backup.retention_days',
                'value' => json_encode(30),
                'group' => 'backup'
            ],
        ];

        // Insert settings into the database
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
} 