<x-app-layout>
    <x-slot name="title">Profil</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Akun</span></x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Profil & password"
            :description="auth()->user()->roleLabel().(auth()->user()->unit ? ' · '.auth()->user()->unit->nama_unit : '')" />

        <x-ui.card>
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-ui.card>

        <x-ui.card id="password">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
