<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edit IKU
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <form action="{{ route('iku.update',$iku->id) }}" method="POST">

          @csrf
          @method('PUT')

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Kode IKU</label>

            <input type="text" name="kode_iku"
              value="{{ $iku->kode_iku }}"
              class="w-full border rounded-lg px-3 py-2 mt-1"
              required>

          </div>

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Nama IKU</label>

            <input type="text" name="nama_iku"
              value="{{ $iku->nama_iku }}"
              class="w-full border rounded-lg px-3 py-2 mt-1"
              required>

          </div>

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>

            <textarea name="deskripsi"
              class="w-full border rounded-lg px-3 py-2 mt-1">{{ $iku->deskripsi }}</textarea>

          </div>

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Kriteria</label>

            <textarea name="kriteria"
              class="w-full border rounded-lg px-3 py-2 mt-1">{{ $iku->kriteria }}</textarea>

          </div>

          <div class="flex justify-end gap-2">

            <a href="{{ route('iku.index') }}"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">

              Batal

            </a>

            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">

              Update

            </button>

          </div>

        </form>

      </div>

    </div>
  </div>

</x-app-layout>