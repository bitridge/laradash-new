@props(['project' => null, 'customers'])

@php
    $projectDetails = old('details');
    if (!$projectDetails && $project && $project->details) {
        $projectDetails = is_array($project->details) ? $project->details : json_decode($project->details, true);
    }
@endphp

<div class="space-y-6" id="projectForm">
    <div>
        <x-input-label for="logo" value="Project Logo" />
        <div class="mt-1 flex items-center space-x-4">
            <div class="flex-shrink-0">
                @if($project && $project->getFirstMediaUrl('logo', 'thumbnail'))
                    <img src="{{ $project->getFirstMediaUrl('logo', 'thumbnail') }}" 
                         alt="{{ $project->name }}" 
                         class="h-12 w-12 rounded-lg object-cover">
                @else
                    <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>
            <div class="flex-grow">
                <input type="file" id="logo" name="logo" accept="image/*" class="hidden" onchange="showPreview(event)">
                <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                    {{ __('Choose Logo') }}
                </label>
            </div>
            <div id="preview-container" class="flex-shrink-0 hidden">
                <img id="preview" class="h-12 w-12 rounded-lg object-cover">
            </div>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('logo')" />
    </div>

    <div>
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select Customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id', $project?->customer_id) == $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }} ({{ $customer->company }})
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('customer_id')" />
    </div>

    <div>
        <x-input-label for="name" value="Project Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project?->name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="website_url" value="Website URL" />
        <x-text-input id="website_url" name="website_url" type="url" class="mt-1 block w-full" :value="old('website_url', $project?->website_url)" required placeholder="https://" />
        <x-input-error class="mt-2" :messages="$errors->get('website_url')" />
    </div>

    <div>
        <x-input-label for="details" value="Project Details" />
        <div id="quill-editor" class="mt-1 block w-full min-h-[200px] bg-white">
            {!! is_array($projectDetails) ? ($projectDetails['content'] ?? '') : $projectDetails !!}
        </div>
        <input type="hidden" name="details" id="details">
        <x-input-error class="mt-2" :messages="$errors->get('details')" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach(['active' => 'Active', 'paused' => 'Paused', 'completed' => 'Completed'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $project?->status) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>

        <div>
            <x-input-label for="start_date" value="Start Date" />
            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', $project?->start_date?->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
        </div>
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Logo preview functionality
    function showPreview(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            const previewContainer = document.getElementById('preview-container');
            const preview = document.getElementById('preview');
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            
            reader.readAsDataURL(file);
        }
    }

    // Initialize Quill editor
    document.addEventListener('DOMContentLoaded', function() {
        var quill = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link', 'code-block'],
                    ['clean']
                ]
            },
            placeholder: 'Write project details here...'
        });

        // Handle form submission
        const form = document.getElementById('projectForm').closest('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = quill.root.innerHTML;
            const plainText = quill.getText().trim();
            
            // Check if the content is empty
            if (!plainText) {
                alert('Please enter project details.');
                return false;
            }
            
            // Set the content value
            document.getElementById('details').value = JSON.stringify({
                content: content,
                plainText: plainText
            });
            
            // Submit the form
            this.submit();
        });

        // Load existing content if available
        @if(old('details'))
            try {
                const oldContent = @json(old('details'));
                if (typeof oldContent === 'object' && oldContent.content) {
                    quill.root.innerHTML = oldContent.content;
                } else if (typeof oldContent === 'string') {
                    try {
                        const parsedContent = JSON.parse(oldContent);
                        if (parsedContent.content) {
                            quill.root.innerHTML = parsedContent.content;
                        }
                    } catch (e) {
                        quill.root.innerHTML = oldContent;
                    }
                }
            } catch (e) {
                console.error('Error loading old content:', e);
            }
        @endif
    });
</script>

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