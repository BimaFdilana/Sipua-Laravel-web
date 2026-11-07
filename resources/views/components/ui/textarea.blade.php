@props(['name', 'value' => null, 'rows' => 3])

<textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}"
    @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
    {{ $attributes->class([
        'block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset',
        'ring-slate-300 focus:ring-blue-600' => ! $errors->has($name),
        'ring-red-400 focus:ring-red-500' => $errors->has($name),
    ]) }}>{{ old($name, $value) }}</textarea>
