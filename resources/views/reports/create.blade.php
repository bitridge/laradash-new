@php
use App\Models\Report;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Generate Report') }}
            </h2>
            <a href="{{ route('projects.show', request('project_id')) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                {{ __('Back to Project') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ request('project_id') }}">

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Report Information') }}</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="title" :value="__('Report Title')" />
                                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Report Description')" />
                                    <div id="description-editor" class="mt-1 block w-full min-h-[200px] bg-white">
                                        {!! old('description.content') ?? '' !!}
                                    </div>
                                    <input type="hidden" name="description" id="description-input">
                                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                </div>
                            </div>
                        </div>

                        <!-- Report Sections -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Report Sections') }}</h3>
                                <button type="button" onclick="addSection()" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md text-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ __('Add Section') }}
                                </button>
                            </div>
                            <div id="sections-container" class="space-y-6">
                                <!-- Sections will be added here dynamically -->
                            </div>
                        </div>

                        <!-- SEO Logs Selection -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Include SEO Logs') }}</h3>
                            <div class="space-y-4">
                                @foreach($seoLogs as $log)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="seo_logs[]" value="{{ $log->id }}" 
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                        <div class="ml-3">
                                            <label class="text-sm font-medium text-gray-700">
                                                {{ $log->title }}
                                                <span class="text-sm text-gray-500">
                                                    ({{ $log->date->format('M j, Y') }} - {{ $log->log_type }})
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                {{ __('Generate Report') }}
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
            // Initialize Quill editor for description
            let descriptionQuill = null;
            let sectionQuills = {};
            let sectionCount = 0;

            // Function to initialize a Quill editor
            function initQuill(elementId, options = {}) {
                return new Quill(elementId, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'color': [] }, { 'background': [] }],
                            ['link', 'image', 'code-block'],
                            ['clean']
                        ],
                        ...options
                    },
                    placeholder: 'Start writing here...'
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Initialize description editor
                descriptionQuill = initQuill('#description-editor');

                // Function to add a new section
                window.addSection = function() {
                    const container = document.getElementById('sections-container');
                    const sectionId = `section-${sectionCount}`;
                    
                    const sectionHtml = `
                        <div class="bg-gray-50 p-4 rounded-lg" id="${sectionId}">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-grow mr-4">
                                    <x-input-label :value="__('Section Title')" />
                                    <x-text-input name="sections[${sectionCount}][title]" type="text" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label :value="__('Order')" />
                                    <x-text-input name="sections[${sectionCount}][order]" type="number" class="mt-1 block w-20" value="${sectionCount + 1}" required />
                                </div>
                                <button type="button" onclick="removeSection('${sectionId}')" class="ml-4 text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <x-input-label :value="__('Content')" />
                                <div id="section-editor-${sectionCount}" class="mt-1 block w-full min-h-[200px] bg-white"></div>
                                <input type="hidden" name="sections[${sectionCount}][content]" id="section-content-${sectionCount}">
                            </div>
                            <div class="mt-4">
                                <x-input-label :value="__('Section Image (Optional)')" />
                                <input type="file" name="sections[${sectionCount}][image]" accept="image/*" class="mt-1">
                            </div>
                        </div>
                    `;
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = sectionHtml;
                    container.appendChild(tempDiv.firstElementChild);

                    // Initialize Quill editor for the new section
                    sectionQuills[sectionId] = initQuill(`#section-editor-${sectionCount}`);
                    sectionCount++;
                };

                // Function to remove a section
                window.removeSection = function(sectionId) {
                    const section = document.getElementById(sectionId);
                    if (section) {
                        // Destroy Quill instance if it exists
                        if (sectionQuills[sectionId]) {
                            delete sectionQuills[sectionId];
                        }
                        section.remove();
                    }
                };

                // Handle form submission
                document.querySelector('form').addEventListener('submit', function(e) {
                    e.preventDefault();

                    try {
                        // Store description content
                        if (descriptionQuill) {
                            const descriptionContent = descriptionQuill.root.innerHTML.trim();
                            const descriptionPlainText = descriptionQuill.getText().trim();
                            
                            if (!descriptionPlainText) {
                                alert('Please enter a report description');
                                return;
                            }

                            // Set the description input value
                            const descriptionData = {
                                content: descriptionContent,
                                plainText: descriptionPlainText
                            };
                            document.getElementById('description-input').value = JSON.stringify(descriptionData);
                        } else {
                            alert('Error: Description editor not initialized');
                            return;
                        }

                        // Check if at least one section exists
                        const sections = document.querySelectorAll('[id^="section-"]');
                        if (sections.length === 0) {
                            alert('Please add at least one section to the report');
                            return;
                        }

                        // Store section contents
                        let hasError = false;
                        sections.forEach(section => {
                            const sectionId = section.id;
                            const sectionNum = sectionId.split('-')[1];
                            const quill = sectionQuills[sectionId];
                            const contentInput = document.getElementById(`section-content-${sectionNum}`);
                            
                            if (quill && contentInput) {
                                const content = quill.root.innerHTML.trim();
                                const plainText = quill.getText().trim();
                                
                                if (!plainText) {
                                    alert(`Please enter content for section ${parseInt(sectionNum) + 1}`);
                                    hasError = true;
                                    return;
                                }

                                contentInput.value = content;
                            }
                        });

                        if (hasError) {
                            return;
                        }

                        // Submit the form
                        this.submit();
                    } catch (error) {
                        console.error('Form submission error:', error);
                        alert('An error occurred while submitting the form. Please try again.');
                    }
                });

                // Add initial section automatically
                addSection();

                // Load old content if it exists
                @if(old('description'))
                    try {
                        const oldContent = @json(old('description'));
                        if (oldContent && descriptionQuill) {
                            if (typeof oldContent === 'string') {
                                const parsedContent = JSON.parse(oldContent);
                                if (parsedContent.content) {
                                    descriptionQuill.root.innerHTML = parsedContent.content;
                                }
                            } else if (oldContent.content) {
                                descriptionQuill.root.innerHTML = oldContent.content;
                            }
                        }
                    } catch (e) {
                        console.error('Error loading old content:', e);
                    }
                @endif
            });
        </script>
    @endpush
</x-app-layout> 