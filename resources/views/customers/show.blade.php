<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer Details') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    {{ __('Edit') }}
                </a>
                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Basic Information') }}</h3>
                            <div class="mt-4 flex items-center">
                                <div class="flex-shrink-0 h-20 w-20">
                                    @if($customer->getFirstMediaUrl('logo'))
                                        <img class="h-20 w-20 rounded-full object-cover" src="{{ $customer->getFirstMediaUrl('logo') }}" alt="{{ $customer->name }}">
                                    @else
                                        <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-2xl font-medium text-gray-500">{{ substr($customer->name, 0, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-6">
                                    <dl class="space-y-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->email }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->phone ?? '-' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Company Information') }}</h3>
                            <dl class="mt-4 space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Company Name') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->company ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Address') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $customer->address ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Assigned SEO Providers') }}</h3>
                        <div class="mt-4">
                            @if($customer->seoProviders->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($customer->seoProviders as $provider)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                            {{ $provider->name }}
                                            <span class="ml-2 text-xs text-indigo-600">{{ $provider->email }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('No SEO providers assigned to this customer.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Projects') }}</h3>
                            <a href="{{ route('projects.create', ['customer_id' => $customer->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                {{ __('Add Project') }}
                            </a>
                        </div>
                        @if($customer->projects->count() > 0)
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Website</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SEO Providers</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($customer->projects as $project)
                                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('projects.show', $project) }}'">
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $project->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="{{ $project->website_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900" onclick="event.stopPropagation();">
                                                        {{ $project->website_url }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($project->status === 'active') bg-green-100 text-green-800
                                                        @elseif($project->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @else bg-red-100 text-red-800
                                                        @endif">
                                                        {{ ucfirst($project->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $project->start_date->format('Y-m-d') }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($project->seoProviders as $provider)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                                {{ $provider->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-gray-500">{{ __('No projects found for this customer.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 