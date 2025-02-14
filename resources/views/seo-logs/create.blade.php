@php
use App\Models\SeoLog;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create SEO Log') }}
            </h2>
            <a href="{{ route('seo-logs.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                {{ __('Back to Logs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('seo-logs.store') }}" enctype="multipart/form-data" id="seoLogForm">
                        @csrf

                        <div class="mb-4">
                            <label for="project_id" class="block text-sm font-medium text-gray-700">{{ __('Project') }}</label>
                            <select name="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" {{ request()->has('project_id') ? 'disabled' : '' }}>
                                <option value="">{{ __('Select a project') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ (old('project_id', request('project_id')) == $project->id) ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(request()->has('project_id'))
                                <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                            @endif
                            @error('project_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="log_type" class="block text-sm font-medium text-gray-700">{{ __('Log Type') }}</label>
                            <select name="log_type" id="log_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('Select a type') }}</option>
                                @foreach(SeoLog::TYPES as $key => $type)
                                    <option value="{{ $key }}" {{ old('log_type') == $key ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('log_type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="date" class="block text-sm font-medium text-gray-700">{{ __('Date') }}</label>
                            <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">{{ __('Content') }}</label>
                            <div id="quill-editor" class="mt-1 block w-full min-h-[200px] bg-white">
                                {!! old('content.content') !!}
                            </div>
                            <input type="hidden" name="content" id="content">
                            @error('content')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="attachments" class="block text-sm font-medium text-gray-700">{{ __('Attachments') }}</label>
                            <input type="file" name="attachments[]" id="attachments" multiple 
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('attachments.*')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Create Log') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link', 'image', 'code-block'],
                        ['clean']
                    ]
                },
                placeholder: 'Write your SEO log content here...'
            });

            // Store Quill's content in the hidden input before form submission
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('content').value = JSON.stringify({
                    content: quill.root.innerHTML,
                    plainText: quill.getText().trim()
                });
            });

            // If there's old content from validation error, load it
            @if(old('content'))
                try {
                    const oldContent = @json(old('content'));
                    if (typeof oldContent === 'object' && oldContent.content) {
                        quill.root.innerHTML = oldContent.content;
                    } else if (typeof oldContent === 'string') {
                        const parsedContent = JSON.parse(oldContent);
                        if (parsedContent.content) {
                            quill.root.innerHTML = parsedContent.content;
                        }
                    }
                } catch (e) {
                    console.error('Error loading old content:', e);
                }
            @endif
        });
    </script>
</x-app-layout> 