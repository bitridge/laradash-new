@php
use App\Models\SeoLog;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('View SEO Log') }}
            </h2>
            <div class="flex space-x-4">
                @can('update', $seoLog)
                <a href="{{ route('seo-logs.edit', $seoLog) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit Log') }}
                </a>
                @endcan
                @can('delete', $seoLog)
                <form action="{{ route('seo-logs.destroy', $seoLog) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to delete this log?')">
                        {{ __('Delete Log') }}
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Log Details') }}</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Project') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $seoLog->project->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Type') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $seoLog->log_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Title') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $seoLog->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Date') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $seoLog->date->format('Y-m-d') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Created By') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $seoLog->user->name }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Content') }}</h3>
                            <div class="prose max-w-none">
                                {!! $seoLog->content !!}
                            </div>
                        </div>
                    </div>

                    @if($seoLog->media->count() > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Attachments') }}</h3>
                        <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($seoLog->media as $media)
                            <li class="relative">
                                <div class="group block w-full aspect-w-10 aspect-h-7 rounded-lg bg-gray-100 overflow-hidden">
                                    @if(in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/gif']))
                                    <img src="{{ $media->getUrl() }}" alt="{{ $media->file_name }}" class="object-cover pointer-events-none">
                                    @else
                                    <div class="flex items-center justify-center h-full">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-2 flex justify-between items-center">
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 truncate">
                                        {{ $media->file_name }}
                                    </a>
                                    @can('update', $seoLog)
                                    <form action="{{ route('seo-logs.delete-attachment', [$seoLog, $media->id]) }}" method="POST" class="flex-shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this attachment?')">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 