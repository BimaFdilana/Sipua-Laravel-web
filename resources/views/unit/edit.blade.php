<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edit Unit / Jurusan
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <h3 class="text-lg font-bold mb-4">Form Edit Unit</h3>

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <form action="{{ route('unit.update', $unit->id) }}" method="POST">
          @csrf
          @method('PUT')

          <!-- Unit ID -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Kode Unit
            </label>

            <input type="text"
              name="unit_id"
              value="{{ old('unit_id', $unit->unit_id) }}"
              class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>

          <!-- Nama Unit -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Nama Unit
            </label>

            <input type="text"
              name="nama_unit"
              value="{{ old('nama_unit', $unit->nama_unit) }}"
              class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>

          <!-- Keterangan -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Keterangan
            </label>

            <textarea
              name="keterangan"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan', $unit->keterangan) }}</textarea>
          </div>

          <!-- Tombol -->
          <div class="flex justify-end gap-2">

            <a href="{{ route('unit.index') }}"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
              Batal
            </a>

            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">

              Update

            </button>

          </div>

        </form>

      </div>

    </div>
  </div>

</x-app-layout>