@props(['name', 'checked' => false, 'label', 'description' => null])

@php($isOn = (bool) old($name, $checked))

<div x-data="{ on: @js($isOn) }" class="flex items-start justify-between gap-4">
    <div>
        <p class="text-sm font-medium text-slate-700" id="{{ $name }}-label">{{ $label }}</p>
        @if ($description)
            <p class="text-xs text-slate-500">{{ $description }}</p>
        @endif
    </div>
    <input type="hidden" name="{{ $name }}" :value="on ? 1 : 0" value="{{ $isOn ? 1 : 0 }}">
    <button type="button" role="switch" :aria-checked="on.toString()" aria-labelledby="{{ $name }}-label" @click="on = ! on"
        :class="on ? 'bg-blue-600' : 'bg-slate-200'"
        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $isOn ? 'bg-blue-600' : 'bg-slate-200' }}">
        <span :class="on ? 'translate-x-5' : 'translate-x-0'"
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 {{ $isOn ? 'translate-x-5' : 'translate-x-0' }}"></span>
    </button>
</div>
