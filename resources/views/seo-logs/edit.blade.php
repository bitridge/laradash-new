@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form action="{{ route('seo-logs.update', $seoLog) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Project Selection -->
                    <div>
                        <x-input-label for="project_id" :value="__('Project')" />
                        <select id="project_id" name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $seoLog->project_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                    </div>

                    <!-- Log Type -->
                    <div>
                        <x-input-label for="log_type" :value="__('Log Type')" />
                        <select id="log_type" name="log_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach(App\Models\SeoLog::TYPES as $value => $label)
                                <option value="{{ $value }}" {{ $seoLog->log_type == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('log_type')" class="mt-2" />
                    </div>

                    <!-- Title -->
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $seoLog->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <!-- Date -->
                    <div>
                        <x-input-label for="date" :value="__('Date')" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $seoLog->date->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>

                    <!-- Content -->
                    <div>
                        <x-input-label for="content" :value="__('Content')" />
                        <div id="content-editor" class="mt-1 block w-full min-h-[200px] bg-white">
                            {!! old('content.content', $seoLog->content['content'] ?? '') !!}
                        </div>
                        <input type="hidden" name="content" id="content-input">
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>

                    <!-- Attachments -->
                    <div>
                        <x-input-label :value="__('Current Attachments')" />
                        <div class="mt-2 space-y-2">
                            @foreach($seoLog->getMedia('attachments') as $media)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span>{{ $media->file_name }}</span>
                                    <button type="button" onclick="deleteAttachment({{ $media->id }})" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <x-input-label for="attachments" :value="__('Add Attachments')" />
                            <input type="file" name="attachments[]" id="attachments" multiple class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>
                            {{ __('Update Log') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-editor {
            min-height: 200px;
            background-color: white;
        }
        .ql-container {
            border: 1px solid #d1d5db;
            border-top: none;
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }
        .ql-toolbar {
            border: 1px solid #d1d5db;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            background-color: white;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        let contentQuill;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            contentQuill = new Quill('#content-editor', {
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
                placeholder: 'Start writing here...'
            });

            // Handle form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault();

                try {
                    const content = contentQuill.root.innerHTML.trim();
                    const plainText = contentQuill.getText().trim();

                    if (!plainText) {
                        alert('Please enter content for the log');
                        return;
                    }

                    document.getElementById('content-input').value = JSON.stringify({
                        content: content,
                        plainText: plainText
                    });

                    this.submit();
                } catch (error) {
                    console.error('Form submission error:', error);
                    alert('An error occurred while submitting the form. Please try again.');
                }
            });
        });

        // Function to delete attachment
        function deleteAttachment(mediaId) {
            if (confirm('Are you sure you want to delete this attachment?')) {
                fetch(`/seo-logs/{{ $seoLog->id }}/attachments/${mediaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Failed to delete attachment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the attachment');
                });
            }
        }
    </script>
@endpush 