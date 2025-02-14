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

                    <div x-data="{ activeTab: 'app' }">
                        <!-- Tabs -->
                        <div class="mb-4 border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button @click="activeTab = 'app'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'app'}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Application Settings
                                </button>
                                <button @click="activeTab = 'smtp'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'smtp'}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    SMTP Settings
                                </button>
                                <button @click="activeTab = 'company'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'company'}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Company Information
                                </button>
                                <button @click="activeTab = 'captcha'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'captcha'}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Captcha Settings
                                </button>
                            </nav>
                        </div>

                        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Application Settings -->
                            <div x-show="activeTab === 'app'">
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
                            <div x-show="activeTab === 'smtp'" class="space-y-4">
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

                            <!-- Company Information -->
                            <div x-show="activeTab === 'company'" class="space-y-4">
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

                            <!-- Captcha Settings -->
                            <div x-show="activeTab === 'captcha'" class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="captcha[enabled]" value="1" {{ old('captcha.enabled', $settings['captcha']['enabled']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-900">Enable Math Captcha on Login Page</label>
                                </div>
                                <p class="text-sm text-gray-500">When enabled, users will need to solve a simple math problem (addition, subtraction, or multiplication with numbers 1-10) to log in.</p>
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
    </script>
    @endpush
</x-app-layout> 