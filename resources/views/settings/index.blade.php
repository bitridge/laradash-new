<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="settings-container">
                        <!-- Tabs -->
                        <div class="mb-4 border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button onclick="switchTab('app')" class="tab-button active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="app">
                                    Application Settings
                                </button>
                                <button onclick="switchTab('smtp')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="smtp">
                                    SMTP Settings
                                </button>
                                <button onclick="switchTab('company')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="company">
                                    Company Information
                                </button>
                                <button onclick="switchTab('captcha')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="captcha">
                                    Captcha Settings
                                </button>
                                <button onclick="switchTab('backup')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="backup">
                                    Backup Settings
                                </button>
                            </nav>
                        </div>

                        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Application Settings -->
                            <div class="tab-content" id="app-tab">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Application Name</label>
                                        <input type="text" name="app[name]" value="{{ old('app.name', $settings['app']['name']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Application Logo</label>
                                        <div class="mt-1 flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                @if($settings['app']['logo'])
                                                    <img src="{{ $settings['app']['logo'] }}" 
                                                         alt="{{ $settings['app']['name'] }}" 
                                                         class="h-12 w-auto object-contain"
                                                         id="current-logo">
                                                @else
                                                    <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow">
                                                <input type="file" id="logo" name="logo" accept="image/*" class="hidden" onchange="showPreview(event)">
                                                <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                                    {{ __('Choose Logo') }}
                                                </label>
                                            </div>
                                            <div id="preview-container" class="flex-shrink-0 hidden">
                                                <img id="preview" class="h-12 w-auto object-contain">
                                            </div>
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                                    </div>
                                </div>
                            </div>

                            <!-- SMTP Settings -->
                            <div class="tab-content hidden" id="smtp-tab">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Sender Name</label>
                                        <input type="text" name="smtp[sender_name]" value="{{ old('smtp.sender_name', $settings['smtp']['sender_name']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email Encryption</label>
                                        <select name="smtp[encryption]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="tls" {{ old('smtp.encryption', $settings['smtp']['encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ old('smtp.encryption', $settings['smtp']['encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                        <input type="text" name="smtp[host]" value="{{ old('smtp.host', $settings['smtp']['host']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                        <input type="number" name="smtp[port]" value="{{ old('smtp.port', $settings['smtp']['port']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                        <input type="text" name="smtp[username]" value="{{ old('smtp.username', $settings['smtp']['username']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Password</label>
                                        <input type="password" name="smtp[password]" value="{{ old('smtp.password', $settings['smtp']['password']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Sender Email</label>
                                        <input type="email" name="smtp[sender_email]" value="{{ old('smtp.sender_email', $settings['smtp']['sender_email']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div class="mt-4 p-4 bg-gray-50 rounded-md">
                                        <h3 class="text-sm font-medium text-gray-900">Send Test Email</h3>
                                        <p class="mt-1 text-sm text-gray-500">Send a test email to verify your SMTP settings.</p>
                                        <div class="mt-3 flex items-center gap-4">
                                            <input type="email" name="test_email" placeholder="Enter email address" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <button type="button" onclick="sendTestEmail(this.form.test_email.value)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Test
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Company Information -->
                            <div class="tab-content hidden" id="company-tab">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Company Name</label>
                                        <input type="text" name="company[name]" value="{{ old('company.name', $settings['company']['name']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Address</label>
                                        <textarea name="company[address]" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('company.address', $settings['company']['address']) }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="company[email]" value="{{ old('company.email', $settings['company']['email']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" name="company[phone]" value="{{ old('company.phone', $settings['company']['phone']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Website URL</label>
                                        <input type="url" name="company[url]" value="{{ old('company.url', $settings['company']['url']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                                        <input type="email" name="company[contact_email]" value="{{ old('company.contact_email', $settings['company']['contact_email']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Captcha Settings -->
                            <div class="tab-content hidden" id="captcha-tab">
                                <div class="flex items-center">
                                    <input type="checkbox" name="captcha[enabled]" value="1" {{ old('captcha.enabled', $settings['captcha']['enabled']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-900">Enable Math Captcha on Login Page</label>
                                </div>
                                <p class="text-sm text-gray-500">When enabled, users will need to solve a simple math problem (addition, subtraction, or multiplication with numbers 1-10) to log in.</p>
                            </div>

                            <!-- Backup Settings -->
                            <div class="tab-content hidden" id="backup-tab">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Database Backup</h3>
                                    
                                    <!-- Manual Backup -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Manual Backup</h4>
                                        <button type="button" onclick="backupDatabase()" 
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Backup Now
                                        </button>
                                    </div>

                                    <!-- Local Backups -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-medium text-gray-700 mb-4">Local Backups</h4>
                                        <div id="backups-list" class="bg-gray-50 rounded-lg p-4">
                                            <div class="animate-pulse flex space-x-4">
                                                <div class="flex-1 space-y-4 py-1">
                                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    <div class="h-4 bg-gray-200 rounded"></div>
                                                    <div class="h-4 bg-gray-200 rounded"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Automated Backup Settings -->
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-medium text-gray-700">Automated Backup Settings</h4>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" name="backup[enabled]" value="1" 
                                                   {{ old('backup.enabled', $settings['backup']['enabled'] ?? false) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 block text-sm text-gray-900">Enable Automated Backups</label>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Backup Frequency</label>
                                                <select name="backup[frequency]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="daily" {{ old('backup.frequency', $settings['backup']['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                                    <option value="weekly" {{ old('backup.frequency', $settings['backup']['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                    <option value="monthly" {{ old('backup.frequency', $settings['backup']['frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Time of Day (UTC)</label>
                                                <input type="time" name="backup[time]" 
                                                       value="{{ old('backup.time', $settings['backup']['time'] ?? '00:00') }}"
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>
                                        </div>

                                        <!-- Remote Storage Settings -->
                                        <div class="mt-6">
                                            <h4 class="text-sm font-medium text-gray-700 mb-4">Remote Storage</h4>
                                            
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Storage Type</label>
                                                    <select name="backup[storage_type]" 
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        <option value="">None</option>
                                                        <option value="ftp">FTP Server</option>
                                                        <option value="google_drive">Google Drive</option>
                                                    </select>
                                                </div>

                                                <!-- FTP Settings -->
                                                <div class="ftp-settings hidden space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">FTP Host</label>
                                                        <input type="text" name="backup[ftp_host]" 
                                                               value="{{ old('backup.ftp_host', $settings['backup']['ftp_host'] ?? '') }}"
                                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">FTP Username</label>
                                                        <input type="text" name="backup[ftp_username]" 
                                                               value="{{ old('backup.ftp_username', $settings['backup']['ftp_username'] ?? '') }}"
                                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">FTP Password</label>
                                                        <input type="password" name="backup[ftp_password]" 
                                                               value="{{ old('backup.ftp_password', $settings['backup']['ftp_password'] ?? '') }}"
                                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">FTP Path</label>
                                                        <input type="text" name="backup[ftp_path]" 
                                                               value="{{ old('backup.ftp_path', $settings['backup']['ftp_path'] ?? '/backups') }}"
                                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <button type="button" onclick="testFtpConnection()" 
                                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Test FTP Connection
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Google Drive Settings -->
                                                <div class="google-drive-settings hidden space-y-4">
                                                    @if(!empty($settings['backup']['google_drive_connected']))
                                                        <div class="bg-green-50 p-4 rounded-md">
                                                            <div class="flex">
                                                                <div class="flex-shrink-0">
                                                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <p class="text-sm font-medium text-green-800">
                                                                        Connected to Google Drive
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">Folder Path</label>
                                                            <input type="text" name="backup[google_drive_folder]" 
                                                                   value="{{ old('backup.google_drive_folder', $settings['backup']['google_drive_folder'] ?? '/backups') }}"
                                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        </div>
                                                        <div>
                                                            <button type="button" onclick="disconnectGoogleDrive()" 
                                                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                Disconnect Google Drive
                                                            </button>
                                                        </div>
                                                    @else
                                                        <div>
                                                            <p class="text-sm text-gray-500 mb-4">Connect your Google Drive account to enable backup storage.</p>
                                                            <a href="{{ route('settings.google-drive.connect') }}" 
                                                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                Connect Google Drive
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Retention Settings -->
                                        <div class="mt-6">
                                            <h4 class="text-sm font-medium text-gray-700 mb-4">Retention Settings</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Keep Backups For</label>
                                                    <div class="mt-1 flex rounded-md shadow-sm">
                                                        <input type="number" name="backup[retention_days]" 
                                                               value="{{ old('backup.retention_days', $settings['backup']['retention_days'] ?? 30) }}"
                                                               min="1"
                                                               class="flex-1 min-w-0 block w-full rounded-none rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                                            Days
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-indigo-500', 'text-indigo-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
            }
            
            // Add active class to selected tab button
            const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
            if (selectedButton) {
                selectedButton.classList.remove('border-transparent', 'text-gray-500');
                selectedButton.classList.add('border-indigo-500', 'text-indigo-600');
            }

            // Load backups if backup tab is selected
            if (tabName === 'backup') {
                loadBackups();
            }
        }

        // Initialize the first tab
        document.addEventListener('DOMContentLoaded', function() {
            switchTab('app');
        });

        // Storage type toggle functionality
        const storageTypeSelect = document.querySelector('select[name="backup[storage_type]"]');
        const ftpSettings = document.querySelector('.ftp-settings');
        const googleDriveSettings = document.querySelector('.google-drive-settings');

        if (storageTypeSelect) {
            storageTypeSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                
                // Hide all storage settings
                if (ftpSettings) ftpSettings.classList.add('hidden');
                if (googleDriveSettings) googleDriveSettings.classList.add('hidden');
                
                // Show selected storage settings
                if (selectedValue === 'ftp' && ftpSettings) {
                    ftpSettings.classList.remove('hidden');
                } else if (selectedValue === 'google_drive' && googleDriveSettings) {
                    googleDriveSettings.classList.remove('hidden');
                }
            });

            // Trigger change event on load to set initial state
            storageTypeSelect.dispatchEvent(new Event('change'));
        }

        // Backup functionality
        function backupDatabase() {
            if (!confirm('Are you sure you want to create a backup now?')) {
                return;
            }

            const button = document.querySelector('button[onclick="backupDatabase()"]');
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating Backup...
            `;

            fetch('{{ route('settings.backup.now') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Backup created successfully!');
                    loadBackups();
                } else {
                    throw new Error(data.message || 'Failed to create backup');
                }
            })
            .catch(error => {
                console.error('Backup error:', error);
                alert('Error creating backup: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Backup Now
                `;
            });
        }

        function loadBackups() {
            const container = document.getElementById('backups-list');
            
            // Show loading state
            container.innerHTML = `
                <div class="animate-pulse flex space-x-4">
                    <div class="flex-1 space-y-4 py-1">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded"></div>
                    </div>
                </div>
            `;

            fetch('{{ route('settings.backup.list') }}', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.backups.length === 0) {
                        container.innerHTML = '<p class="text-gray-500 text-sm">No backups available.</p>';
                        return;
                    }

                    const table = document.createElement('table');
                    table.className = 'min-w-full divide-y divide-gray-200';
                    
                    table.innerHTML = `
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                    `;

                    data.backups.forEach(backup => {
                        const size = formatFileSize(backup.size);
                        const date = new Date(backup.created_at).toLocaleString();
                        
                        table.querySelector('tbody').innerHTML += `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${backup.filename}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${size}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${date}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ url('settings/backup/download') }}/${backup.filename}" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-4">Download</a>
                                    <button onclick="deleteBackup('${backup.filename}')"
                                            class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        `;
                    });

                    container.innerHTML = '';
                    container.appendChild(table);
                } else {
                    container.innerHTML = '<p class="text-red-500 text-sm">Failed to load backups: ' + data.message + '</p>';
                }
            })
            .catch(error => {
                container.innerHTML = '<p class="text-red-500 text-sm">Error loading backups: ' + error + '</p>';
            });
        }

        function deleteBackup(filename) {
            if (!confirm('Are you sure you want to delete this backup?')) {
                return;
            }

            fetch(`/settings/backup/${filename}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadBackups();
                } else {
                    alert('Failed to delete backup: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting backup:', error);
                alert('Failed to delete backup: ' + error);
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function sendTestEmail(email) {
            if (!email) {
                alert('Please enter an email address');
                return;
            }

            fetch('{{ route('settings.test-email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ test_email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Failed to send test email: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error sending test email: ' + error);
            });
        }

        function showPreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                const previewContainer = document.getElementById('preview-container');
                const preview = document.getElementById('preview');
                const currentLogo = document.getElementById('current-logo');
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    if (currentLogo) {
                        currentLogo.classList.add('hidden');
                    }
                }
                
                reader.readAsDataURL(file);
            }
        }

        function testFtpConnection() {
            const ftpData = {
                host: document.querySelector('input[name="backup[ftp_host]"]').value,
                username: document.querySelector('input[name="backup[ftp_username]"]').value,
                password: document.querySelector('input[name="backup[ftp_password]"]').value,
                path: document.querySelector('input[name="backup[ftp_path]"]').value
            };

            fetch('{{ route('settings.backup.test-ftp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(ftpData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('FTP connection successful!');
                } else {
                    alert('Failed to connect to FTP: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error testing FTP connection: ' + error);
            });
        }

        function disconnectGoogleDrive() {
            if (!confirm('Are you sure you want to disconnect Google Drive?')) {
                return;
            }

            fetch('{{ route('settings.google-drive.disconnect') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to disconnect: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error disconnecting Google Drive: ' + error);
            });
        }
    </script>
    @endpush
</x-app-layout> 