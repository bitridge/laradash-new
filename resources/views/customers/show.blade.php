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
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Projects') }}</h3>
                        @if($customer->projects->count() > 0)
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Website</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($customer->projects as $project)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $project->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $project->website_url }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $project->status }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $project->start_date->format('Y-m-d') }}</td>
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