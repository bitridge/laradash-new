@props(['customer' => null])

<div class="space-y-6">
    <div>
        <x-input-label for="logo" value="Logo" />
        <div class="mt-1 flex items-center space-x-4">
            <div class="flex-shrink-0">
                @if($customer && $customer->getFirstMediaUrl('logo', 'thumbnail'))
                    <img src="{{ $customer->getFirstMediaUrl('logo', 'thumbnail') }}" alt="{{ $customer->name }}" class="h-12 w-12 rounded-full object-cover">
                @else
                    <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
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
                <img id="preview" class="h-12 w-12 rounded-full object-cover">
            </div>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('logo')" />
    </div>

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer?->email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $customer?->phone)" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="company" value="Company" />
        <x-text-input id="company" name="company" type="text" class="mt-1 block w-full" :value="old('company', $customer?->company)" />
        <x-input-error class="mt-2" :messages="$errors->get('company')" />
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $customer?->address) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>
</div>

@push('scripts')
<script>
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
</script>
@endpush 