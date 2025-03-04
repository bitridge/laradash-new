<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('reports.create', ['project_id' => $project->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('Generate Report') }}
                </a>
                <a href="{{ route('seo-logs.create', ['project_id' => $project->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add SEO Log') }}
                </a>
                <a href="{{ route('projects.edit', $project) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit Project') }}
                </a>
                <a href="{{ route('projects.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    {{ __('Back to Projects') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Project Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Project Info -->
                        <div class="md:col-span-2 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Project Information') }}</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Project Name') }}</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $project->name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Website URL') }}</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <a href="{{ $project->website_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                {{ $project->website_url }}
                                            </a>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                        <p class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 
                                                   ($project->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : 
                                                    'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $project->start_date->format('F j, Y') }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Project Details') }}</label>
                                        <div class="mt-2 prose max-w-none">
                                            {!! $project->details['content'] ?? '' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Project Logo -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ __('Project Logo') }}</h4>
                                <div class="mt-2">
                                    @if($project->getFirstMediaUrl('logo'))
                                        <img src="{{ $project->getFirstMediaUrl('logo', 'preview') }}" 
                                             alt="{{ $project->name }}" 
                                             class="rounded-lg border border-gray-200">
                                    @else
                                        <div class="h-48 w-full rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ __('Customer Information') }}</h4>
                                <div class="mt-2 flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($project->customer->getFirstMediaUrl('logo'))
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="{{ $project->customer->getFirstMediaUrl('logo', 'thumbnail') }}" 
                                                 alt="{{ $project->customer->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('customers.show', $project->customer) }}" class="hover:underline">
                                                {{ $project->customer->name }}
                                            </a>
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $project->customer->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Providers -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ __('SEO Providers') }}</h4>
                                <div class="mt-2 space-y-2">
                                    @foreach($project->seoProviders as $provider)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-gray-900">{{ $provider->name }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Delete Project -->
                            <div class="pt-6 border-t border-gray-200">
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="flex justify-center">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this project?') }}')">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('Delete Project') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Logs Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('SEO Logs') }}</h3>
                        <a href="{{ route('seo-logs.create', ['project_id' => $project->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Add SEO Log') }}
                        </a>
                    </div>

                    @if($project->seoLogs->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Title') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Created By') }}</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">{{ __('Actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($project->seoLogs->sortByDesc('date') as $log)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->date->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $log->log_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->title }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('seo-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('View') }}</a>
                                                @can('update', $log)
                                                    <a href="{{ route('seo-logs.edit', $log) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('Edit') }}</a>
                                                @endcan
                                                @can('delete', $log)
                                                    <form action="{{ route('seo-logs.destroy', $log) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('Are you sure you want to delete this log?') }}')">
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No SEO Logs') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Get started by creating a new SEO log.') }}</p>
                            <div class="mt-6">
                                <a href="{{ route('seo-logs.create', ['project_id' => $project->id]) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ __('Create SEO Log') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    @endpush
</x-app-layout> 