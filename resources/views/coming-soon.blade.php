<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Fitur Dalam Pengembangan
    </h2>
  </x-slot>

  <div class="py-10">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-10 text-center">

        <div class="text-6xl mb-4">
          🚧
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-2">
          Fitur Sedang Dikembangkan
        </h2>

        <p class="text-gray-500 mb-6">
          Menu ini sedang dalam tahap pengembangan pada sistem
          <strong>SIPUA (Sistem Informasi Pengusulan Anggaran)</strong>.
        </p>

        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg inline-block">
          Silakan kembali lagi nanti.
        </div>

        <div class="mt-6">
          <a href="{{ route('dashboard') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">

            ⬅ Kembali ke Dashboard

          </a>
        </div>

      </div>

    </div>
  </div>

</x-app-layout>