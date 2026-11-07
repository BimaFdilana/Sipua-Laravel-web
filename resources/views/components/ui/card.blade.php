@props(['title' => null, 'description' => null, 'padding' => true])

<section {{ $attributes->merge(['class' => 'relative rounded-xl border border-slate-200 bg-white']) }}>
    @if ($title)
        <header class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
            @if ($description)
                <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
    @isset($footer)
        <footer class="flex items-center justify-end gap-2 rounded-b-xl border-t border-slate-200 bg-slate-50 px-5 py-3">
            {{ $footer }}
        </footer>
    @endisset
</section>
