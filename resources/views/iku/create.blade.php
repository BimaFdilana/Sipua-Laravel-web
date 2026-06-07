<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Tambah IKU
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <form action="{{ route('iku.store') }}" method="POST">

          @csrf

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Kode IKU</label>

            <input type="text" name="kode_iku"
              class="w-full border rounded-lg px-3 py-2 mt-1"
              required>

          </div>

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Nama IKU</label>

            <input type="text" name="nama_iku"
              class="w-full border rounded-lg px-3 py-2 mt-1"
              required>

          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Sifat IKU</label>

            <select name="sifat" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="Wajib">Wajib</option>
              <option value="Pilihan">Pilihan</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Tahun</label>
            <input type="number" name="tahun" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Target</label>
            <input type="text" name="target" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>

            <textarea name="deskripsi"
              class="w-full border rounded-lg px-3 py-2 mt-1"></textarea>

          </div>

          <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700">Kriteria</label>

            <textarea name="kriteria"
              class="w-full border rounded-lg px-3 py-2 mt-1"></textarea>

          </div>

          <div class="flex justify-end gap-2">

            <a href="{{ route('iku.index') }}"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">

              Batal

            </a>

            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">

              Simpan

            </button>

          </div>

        </form>

      </div>

    </div>
  </div>

</x-app-layout>