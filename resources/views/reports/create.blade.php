@php
use App\Models\Report;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Generate Report') }}
            </h2>
            @if(request('project_id'))
                <a href="{{ route('projects.show', request('project_id')) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-semibold text-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    {{ __('Back to Project') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        @if(request('project_id'))
                            <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                        @else
                            <div class="mb-4">
                                <x-input-label for="project_id" :value="__('Project')" />
                                <select name="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="">{{ __('Select a project') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
                            </div>
                        @endif

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Report Information') }}</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="title" :value="__('Report Title')" />
                                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Report Description')" />
                                    <textarea id="description" name="description" rows="6" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required>{{ old('description') }}</textarea>
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
                        @if($seoLogs->isNotEmpty())
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Include SEO Logs') }}</h3>
                                <div class="space-y-4">
                                    @foreach($seoLogs as $log)
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="seo_log_ids[]" value="{{ $log->id }}" 
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
                        @endif

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

    @push('scripts')
        <script>
            let sectionCount = 0;

            document.addEventListener('DOMContentLoaded', function() {
                // Add initial section
                addSection();

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
                                <textarea name="sections[${sectionCount}][content]" rows="6" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required></textarea>
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
                    sectionCount++;
                };

                // Function to remove a section
                window.removeSection = function(sectionId) {
                    const section = document.getElementById(sectionId);
                    if (section) {
                        section.remove();
                    }
                };

                // Handle form submission
                document.querySelector('form').addEventListener('submit', function(e) {
                    // Check if at least one section exists
                    const sections = document.querySelectorAll('[id^="section-"]');
                    if (sections.length === 0) {
                        e.preventDefault();
                        alert('Please add at least one section to the report');
                        return false;
                    }

                    // Check if all required fields are filled
                    const title = document.getElementById('title').value.trim();
                    const description = document.getElementById('description').value.trim();
                    const projectId = document.querySelector('input[name="project_id"]')?.value || document.getElementById('project_id')?.value;

                    if (!title || !description || !projectId) {
                        e.preventDefault();
                        alert('Please fill in all required fields');
                        return false;
                    }

                    // Check if all section fields are filled
                    let allSectionsFilled = true;
                    sections.forEach(section => {
                        const title = section.querySelector('input[name$="[title]"]').value.trim();
                        const content = section.querySelector('textarea[name$="[content]"]').value.trim();
                        const order = section.querySelector('input[name$="[order]"]').value.trim();

                        if (!title || !content || !order) {
                            allSectionsFilled = false;
                        }
                    });

                    if (!allSectionsFilled) {
                        e.preventDefault();
                        alert('Please fill in all section fields');
                        return false;
                    }
                });
            });
        </script>
    @endpush
</x-app-layout> 