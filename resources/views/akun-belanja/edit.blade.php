<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edit Akun Belanja
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <form action="{{ route('akun-belanja.update',$akun->kode_akun) }}" method="POST">

          @csrf
          @method('PUT')

          <div class="mb-4">
            <label class="block text-sm font-medium">Kode Akun</label>
            <input type="text" name="kode_akun"
              value="{{ $akun->kode_akun }}"
              class="w-full border rounded-lg px-3 py-2"
              readonly>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium">Nama Akun</label>
            <input type="text" name="nama_akun"
              value="{{ $akun->nama_akun }}"
              class="w-full border rounded-lg px-3 py-2"
              required>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium">Jenis Belanja</label>

            <select name="jenis_belanja"
              class="w-full border rounded-lg px-3 py-2">

              <option value="Barang" {{ $akun->jenis_belanja == 'Barang' ? 'selected' : '' }}>
                Barang
              </option>

              <option value="Jasa" {{ $akun->jenis_belanja == 'Jasa' ? 'selected' : '' }}>
                Jasa
              </option>

              <option value="Modal" {{ $akun->jenis_belanja == 'Modal' ? 'selected' : '' }}>
                Modal
              </option>

            </select>

          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Keterangan
            </label>

            <textarea
              name="keterangan"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan', $akun->keterangan) }}</textarea>
          </div>
          <div class="flex justify-end gap-2">

            <a href="{{ route('akun-belanja.index') }}"
              class="bg-gray-500 text-white px-4 py-2 rounded">
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