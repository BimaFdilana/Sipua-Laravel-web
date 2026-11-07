@props(['name', 'options' => [], 'value' => null, 'placeholder' => null])

{{-- $options: [nilai => label]. Nilai terpilih memakai old() lebih dulu. --}}
@php($selected = (string) old($name, $value))

<select name="{{ $name }}" id="{{ $name }}"
    @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
    {{ $attributes->class([
        'block w-full rounded-md border-0 py-2 pl-3 pr-9 text-sm text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset',
        'ring-slate-300 focus:ring-blue-600' => ! $errors->has($name),
        'ring-red-400 focus:ring-red-500' => $errors->has($name),
    ]) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $optValue => $optLabel)
        <option value="{{ $optValue }}" @selected($selected === (string) $optValue)>{{ $optLabel }}</option>
    @endforeach
</select>
