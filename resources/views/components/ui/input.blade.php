@props(['name', 'value' => null, 'type' => 'text'])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
    @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
    @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
    {{ $attributes->class([
        'block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset disabled:bg-slate-50 disabled:text-slate-500',
        'ring-slate-300 focus:ring-blue-600' => ! $errors->has($name),
        'ring-red-400 focus:ring-red-500' => $errors->has($name),
    ]) }}>
