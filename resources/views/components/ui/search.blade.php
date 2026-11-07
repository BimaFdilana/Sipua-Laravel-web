@props(['placeholder' => 'Cari…'])

{{-- Form pencarian GET (?q=). Slot opsional untuk filter tambahan (select, dsb.). --}}
<form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
    <div class="relative flex-1">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}" aria-label="Cari"
            class="block w-full rounded-md border-0 py-2 pl-9 pr-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600">
    </div>
    {{ $slot }}
    <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
    @if (collect(request()->except('page'))->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
        <x-ui.button :href="url()->current()" variant="ghost">Reset</x-ui.button>
    @endif
</form>
