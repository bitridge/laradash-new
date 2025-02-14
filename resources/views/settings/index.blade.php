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
                                        <input type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        @if($settings['app']['logo'])
                                            <div class="mt-2">
                                                <img src="{{ $settings['app']['logo'] }}" alt="Current logo" class="h-12">
                                            </div>
                                        @endif
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
    </script>
    @endpush
</x-app-layout> 