<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Actions -->
            <div class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('seo-logs.create') }}" 
                   class="flex items-center justify-center px-4 py-3 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg shadow-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-semibold">Add SEO Log</span>
                </a>

                <a href="{{ route('customers.create') }}" 
                   class="flex items-center justify-center px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span class="font-semibold">Add Customer</span>
                </a>

                <a href="{{ route('projects.create') }}" 
                   class="flex items-center justify-center px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="font-semibold">Add Project</span>
                </a>

                @if(auth()->user()->isAdmin())
                <a href="{{ route('users.create') }}" 
                   class="flex items-center justify-center px-4 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-lg shadow-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span class="font-semibold">Add User</span>
                </a>
                @endif
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Customers</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['total_customers'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Projects</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['total_projects'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Active Projects</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['active_projects'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total SEO Logs</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['total_seo_logs'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Reports</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['total_reports'] }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent SEO Logs -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent SEO Logs</h3>
                        <div class="space-y-4">
                            @foreach($recentSeoLogs as $log)
                                <div class="border-l-4 border-indigo-400 pl-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <a href="{{ route('seo-logs.show', $log) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                                {{ $log->title }}
                                            </a>
                                            <p class="text-sm text-gray-500">
                                                {{ $log->project->name }} • {{ $log->date->format('M j, Y') }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                            {{ $log->log_type }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Recent Reports -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Reports</h3>
                        <div class="space-y-4">
                            @foreach($recentReports as $report)
                                <div class="border-l-4 border-purple-400 pl-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <a href="{{ route('reports.show', $report) }}" class="text-sm font-medium text-gray-900 hover:text-purple-600">
                                                {{ $report->title }}
                                            </a>
                                            <p class="text-sm text-gray-500">
                                                {{ $report->project->name }} • {{ $report->generated_at->format('M j, Y') }}
                                            </p>
                                        </div>
                                        <span class="text-sm text-gray-500">
                                            by {{ $report->generatedBy->name }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
