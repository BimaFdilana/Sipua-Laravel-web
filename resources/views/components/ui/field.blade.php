@props(['label', 'name', 'required' => false, 'hint' => null])

{{-- Pembungkus label + kontrol + pesan error. Isi slot dengan <x-ui.input>, <x-ui.select>, atau <x-ui.textarea>. --}}
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-500" aria-hidden="true">*</span>
        @endif
    </label>
    {{ $slot }}
    @error($name)
        <p class="text-sm text-red-600" id="{{ $name }}-error">{{ $message }}</p>
    @elseif ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @enderror
</div>
