@props(['seoLog' => null, 'projects', 'logTypes'])

@php
    $content = old('content');
    $actionItems = old('action_items');
    $recommendations = old('recommendations');
    
    if (!$content && $seoLog && $seoLog->content) {
        $content = is_array($seoLog->content) ? $seoLog->content : json_decode($seoLog->content, true);
    }
    if (!$actionItems && $seoLog && $seoLog->action_items) {
        $actionItems = is_array($seoLog->action_items) ? $seoLog->action_items : json_decode($seoLog->action_items, true);
    }
    if (!$recommendations && $seoLog && $seoLog->recommendations) {
        $recommendations = is_array($seoLog->recommendations) ? $seoLog->recommendations : json_decode($seoLog->recommendations, true);
    }
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="project_id" value="Project" />
        <select id="project_id" name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select Project</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" {{ old('project_id', $seoLog?->project_id) == $project->id ? 'selected' : '' }}>
                    {{ $project->name }} ({{ $project->customer->name }})
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
    </div>

    <div>
        <x-input-label for="log_type" value="Log Type" />
        <select id="log_type" name="log_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select Type</option>
            @foreach($logTypes as $value => $label)
                <option value="{{ $value }}" {{ old('log_type', $seoLog?->log_type) == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('log_type')" />
    </div>

    <div>
        <x-input-label for="title" value="Title" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $seoLog?->title)" required />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="date" value="Date" />
        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $seoLog?->date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error class="mt-2" :messages="$errors->get('date')" />
    </div>

    <div>
        <x-input-label for="content" value="Content" />
        <div id="content-editor" class="mt-1 block w-full min-h-[200px] bg-white">
            {!! $content['content'] ?? '' !!}
        </div>
        <input type="hidden" name="content" id="content">
        <x-input-error class="mt-2" :messages="$errors->get('content')" />
    </div>

    <div>
        <x-input-label for="action_items" value="Action Items" />
        <div id="action-items-editor" class="mt-1 block w-full min-h-[200px] bg-white">
            {!! $actionItems['content'] ?? '' !!}
        </div>
        <input type="hidden" name="action_items" id="action_items">
        <x-input-error class="mt-2" :messages="$errors->get('action_items')" />
    </div>

    <div>
        <x-input-label for="recommendations" value="Recommendations" />
        <div id="recommendations-editor" class="mt-1 block w-full min-h-[200px] bg-white">
            {!! $recommendations['content'] ?? '' !!}
        </div>
        <input type="hidden" name="recommendations" id="recommendations">
        <x-input-error class="mt-2" :messages="$errors->get('recommendations')" />
    </div>

    <div>
        <x-input-label for="attachments" value="Attachments" />
        <div class="mt-2">
            <input type="file" name="attachments[]" id="attachments" multiple 
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('attachments.*')" />

        @if($seoLog && $seoLog->getMedia('attachments')->isNotEmpty())
            <div class="mt-4 grid grid-cols-2 gap-4">
                @foreach($seoLog->getMedia('attachments') as $media)
                    <div class="relative group">
                        <img src="{{ $media->getUrl('thumbnail') }}" 
                             alt="{{ $media->name }}" 
                             class="w-full h-32 object-cover rounded-lg">
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
                            <button type="button" 
                                    onclick="deleteAttachment('{{ $media->id }}')"
                                    class="text-white hover:text-red-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set today's date by default
        if (!document.getElementById('date').value) {
            document.getElementById('date').value = new Date().toISOString().split('T')[0];
        }

        // Initialize Quill editors
        const editors = {
            content: new Quill('#content-editor', {
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
            }),
            action_items: new Quill('#action-items-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                },
                placeholder: 'List your action items here...'
            }),
            recommendations: new Quill('#recommendations-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                },
                placeholder: 'Write your recommendations here...'
            })
        };

        // Store Quill's content in hidden inputs before form submission
        document.querySelector('form').addEventListener('submit', function() {
            for (const [key, editor] of Object.entries(editors)) {
                document.getElementById(key).value = JSON.stringify({
                    content: editor.root.innerHTML,
                    plainText: editor.getText().trim()
                });
            }
        });
    });

    // Function to delete attachments
    function deleteAttachment(mediaId) {
        if (confirm('Are you sure you want to delete this attachment?')) {
            fetch(`/seo-logs/{{ $seoLog?->id }}/attachments/${mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the attachment from the UI
                    const attachmentElement = document.querySelector(`[data-media-id="${mediaId}"]`);
                    if (attachmentElement) {
                        attachmentElement.remove();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script> 