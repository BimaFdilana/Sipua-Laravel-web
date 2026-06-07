<x-app-layout>

    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard SIPUA

        </h1>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- GRID CARD -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Tahun Anggaran -->
                <div class="bg-white shadow-lg rounded-xl p-6 border-l-4 border-blue-500">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-gray-500 text-sm">Tahun Anggaran Aktif</p>

                            <h3 class="text-2xl font-bold text-gray-800">

                                {{ $tahunAktif->tahun ?? '-' }}

                            </h3>

                        </div>

                        <div class="text-4xl">
                            📅
                        </div>

                    </div>

                </div>


                <!-- Jumlah Unit -->
                <div class="bg-white shadow-lg rounded-xl p-6 border-l-4 border-green-500">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-gray-500 text-sm">Jumlah Unit</p>

                            <h3 class="text-2xl font-bold text-gray-800">

                                {{ $jumlahUnit }}

                            </h3>

                        </div>

                        <div class="text-4xl">
                            🏢
                        </div>

                    </div>

                </div>


                <!-- Jumlah User -->
                <div class="bg-white shadow-lg rounded-xl p-6 border-l-4 border-purple-500">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-gray-500 text-sm">Jumlah User</p>

                            <h3 class="text-2xl font-bold text-gray-800">

                                {{ $jumlahUser }}

                            </h3>

                        </div>

                        <div class="text-4xl">
                            👤
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>