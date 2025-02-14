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
            'app.logo' => 'nullable|image|max:1024',
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
        if ($request->hasFile('app.logo')) {
            $path = $request->file('app.logo')->store('public/logo');
            $validated['app']['logo'] = Storage::url($path);
        }

        // Save settings by group
        foreach ($validated as $group => $values) {
            Setting::set($group, $values, $group);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $smtp = Setting::get('smtp');
            config([
                'mail.mailers.smtp.host' => $smtp['host'],
                'mail.mailers.smtp.port' => $smtp['port'],
                'mail.mailers.smtp.encryption' => $smtp['encryption'],
                'mail.mailers.smtp.username' => $smtp['username'],
                'mail.mailers.smtp.password' => $smtp['password'],
                'mail.from.address' => $smtp['sender_email'],
                'mail.from.name' => $smtp['sender_name'],
            ]);

            Mail::raw('This is a test email from your application.', function($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('Test Email');
            });

            return back()->with('success', 'Test email sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
} 