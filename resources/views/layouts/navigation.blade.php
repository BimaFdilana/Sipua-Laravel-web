@php($user = Auth::user())

{{-- Sidebar: tetap di layar besar, off-canvas di layar kecil --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-out lg:translate-x-0">

    <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-8 object-contain">
            <span class="leading-tight">
                <span class="block text-sm font-bold tracking-wide text-slate-900">SIPUA</span>
                <span class="block text-[11px] text-slate-500">Usulan Anggaran</span>
            </span>
        </a>
        <button type="button" @click="sidebarOpen = false" class="ml-auto rounded-md p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden">
            <span class="sr-only">Tutup menu</span>
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Menu utama">
        @foreach (\App\Support\Menu::for($user) as $group)
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $group['heading'] }}</p>
                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php($active = request()->routeIs($item['active']))
                        <li>
                            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                                @class([
                                    'group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    'bg-blue-50 text-blue-700' => $active,
                                    'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
                                ])>
                                <x-icon :name="$item['icon']" @class(['h-5 w-5', 'text-blue-600' => $active, 'text-slate-400 group-hover:text-slate-600' => ! $active]) />
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Akun --}}
    <div x-data="{ open: false }" class="relative border-t border-slate-200 p-3">
        <button type="button" @click="open = ! open" @click.outside="open = false" :aria-expanded="open"
            class="flex w-full items-center gap-3 rounded-md px-2 py-2 text-left hover:bg-slate-100">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-800 text-sm font-semibold text-white">
                {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium text-slate-900">{{ $user->name }}</span>
                <span class="block truncate text-xs text-slate-500">{{ $user->roleLabel() }}@if ($user->unit) · {{ $user->unit->unit_id }}@endif</span>
            </span>
            <x-icon name="chevron-down" class="h-4 w-4 text-slate-400 transition-transform" ::class="open && 'rotate-180'" />
        </button>

        <div x-show="open" x-cloak x-transition.origin.bottom
            class="absolute bottom-full left-3 right-3 mb-2 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                <x-icon name="user" class="h-4 w-4 text-slate-400" /> Profil & password
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                    <x-icon name="logout" class="h-4 w-4" /> Keluar
                </button>
            </form>
        </div>
    </div>
</aside>
