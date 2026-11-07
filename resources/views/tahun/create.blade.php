<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Tambah Tahun Anggaran
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <h3 class="text-lg font-bold mb-4">Form Tambah Tahun Anggaran</h3>

        {{-- Error Validation --}}
        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <form action="{{ route('tahun.store') }}" method="POST">
          @csrf

          <!-- Tahun -->
          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">
              Tahun Anggaran
            </label>

            <input type="number"
              name="tahun"
              value="{{ old('tahun') }}"
              placeholder="Contoh: 2026"
              class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">

          </div>

          <!-- Status -->
          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">
              Status
            </label>

            <select name="status"
              class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">

              <option value="1">Aktif</option>
              <option value="0">Tidak Aktif</option>

            </select>

          </div>

          <!-- Tombol -->
          <div class="flex justify-end gap-2">

            <a href="{{ route('tahun.index') }}"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">

              Batal

            </a>

            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">

              Simpan

            </button>

          </div>

        </form>

      </div>

    </div>
  </div>

</x-app-layout>