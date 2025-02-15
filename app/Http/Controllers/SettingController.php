<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app' => Setting::get('app', [
                'name' => config('app.name'),
                'logo' => null,
            ]),
            'smtp' => Setting::get('smtp', [
                'sender_name' => '',
                'encryption' => 'tls',
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => '',
                'sender_email' => '',
            ]),
            'company' => Setting::get('company', [
                'name' => '',
                'address' => '',
                'email' => '',
                'phone' => '',
                'url' => '',
                'contact_email' => '',
            ]),
            'captcha' => Setting::get('captcha', [
                'enabled' => false,
            ]),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app.name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:1024',
            'smtp.sender_name' => 'required|string|max:255',
            'smtp.encryption' => 'required|in:tls,ssl',
            'smtp.host' => 'required|string|max:255',
            'smtp.port' => 'required|numeric',
            'smtp.username' => 'required|string|max:255',
            'smtp.password' => 'required|string|max:255',
            'smtp.sender_email' => 'required|email',
            'company.name' => 'required|string|max:255',
            'company.address' => 'required|string',
            'company.email' => 'required|email',
            'company.phone' => 'required|string|max:20',
            'company.url' => 'required|url',
            'company.contact_email' => 'required|email',
            'captcha.enabled' => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            $appSettings = Setting::get('app', []);
            if (isset($appSettings['logo'])) {
                $oldPath = str_replace('/storage/', 'public/', $appSettings['logo']);
                Storage::delete($oldPath);
            }

            // Store new logo
            $path = $request->file('logo')->store('public/logo');
            $appSettings = $validated['app'] ?? [];
            $appSettings['logo'] = Storage::url($path);
            $validated['app'] = $appSettings;
        }

        // Save settings by group
        foreach ($validated as $group => $values) {
            if ($group !== 'logo') { // Skip the logo field as it's already handled
                Setting::set($group, $values, $group);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            // Get SMTP settings
            $smtpSettings = Setting::get('smtp');

            // Configure mail settings
            config([
                'mail.mailers.smtp.host' => $smtpSettings['host'],
                'mail.mailers.smtp.port' => $smtpSettings['port'],
                'mail.mailers.smtp.encryption' => $smtpSettings['encryption'],
                'mail.mailers.smtp.username' => $smtpSettings['username'],
                'mail.mailers.smtp.password' => $smtpSettings['password'],
                'mail.from.address' => $smtpSettings['sender_email'],
                'mail.from.name' => $smtpSettings['sender_name'],
            ]);

            // Send test email
            Mail::raw('This is a test email from your application.', function($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('Test Email');
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function connectGoogleDrive()
    {
        // Initialize Google Client
        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('settings.google-drive.callback'));
        $client->addScope(\Google_Service_Drive::DRIVE_FILE);

        // Generate the URL to request access
        $authUrl = $client->createAuthUrl();

        return redirect($authUrl);
    }

    public function handleGoogleDriveCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.index')
                           ->with('error', 'Failed to connect Google Drive: ' . $request->get('error'));
        }

        try {
            // Initialize Google Client
            $client = new \Google_Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(route('settings.google-drive.callback'));

            // Exchange authorization code for access token
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

            if (isset($token['error'])) {
                throw new \Exception($token['error_description'] ?? $token['error']);
            }

            // Store the token in settings
            $backupSettings = Setting::get('backup', []);
            $backupSettings['google_drive_token'] = $token;
            Setting::set('backup', $backupSettings);

            return redirect()->route('settings.index')
                           ->with('success', 'Google Drive connected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                           ->with('error', 'Failed to connect Google Drive: ' . $e->getMessage());
        }
    }

    public function disconnectGoogleDrive()
    {
        try {
            // Remove Google Drive token from settings
            $backupSettings = Setting::get('backup', []);
            unset($backupSettings['google_drive_token']);
            Setting::set('backup', $backupSettings);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 