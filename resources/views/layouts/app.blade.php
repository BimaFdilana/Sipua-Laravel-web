<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · ' : '' }}SIPUA</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">

            {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
            <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"></div>

            @include('layouts.navigation')

            <div class="lg:pl-64">
                {{-- Top bar --}}
                <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <button type="button" @click="sidebarOpen = true"
                        class="-ml-1 rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden">
                        <span class="sr-only">Buka menu</span>
                        <x-icon name="menu" class="h-6 w-6" />
                    </button>

                    <div class="min-w-0 flex-1">
                        @isset($header)
                            <div class="truncate">{{ $header }}</div>
                        @endisset
                    </div>

                    @if ($tahunAktif = \App\Models\Tahun::where('status', true)->value('tahun'))
                        <span class="hidden items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600 sm:inline-flex">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            TA {{ $tahunAktif }}
                        </span>
                    @endif
                </header>

                {{-- Pesan flash --}}
                @if (session('success') || session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                        class="px-4 pt-4 sm:px-6 lg:px-8">
                        <div @class([
                            'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm',
                            'border-emerald-200 bg-emerald-50 text-emerald-800' => session('success'),
                            'border-red-200 bg-red-50 text-red-800' => session('error'),
                        ]) role="status">
                            <x-icon :name="session('success') ? 'check-circle' : 'alert'" class="mt-0.5 h-5 w-5" />
                            <p class="flex-1">{{ session('success') ?? session('error') }}</p>
                            <button type="button" @click="show = false" class="opacity-60 hover:opacity-100">
                                <span class="sr-only">Tutup</span>
                                <x-icon name="x" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
