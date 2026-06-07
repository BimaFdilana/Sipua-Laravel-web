<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Tambah User
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto">

      <div class="bg-white shadow-md rounded-lg p-6">

        <form action="{{ route('users.store') }}" method="POST">
          @csrf

          <!-- Username -->
          <div class="mb-4">
            <label>nama</label>
            <input type="text" name="name" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label>Email</label>
            <input type="email" name="email" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Password -->
          <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Nama Lengkap -->
          <div class="mb-4">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- NIP -->
          <div class="mb-4">
            <label>NIP</label>
            <input type="text" name="nip" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Jabatan -->
          <div class="mb-4">
            <label>Jabatan</label>
            <input type="text" name="jabatan" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- No HP -->
          <div class="mb-4">
            <label>No HP</label>
            <input type="text" name="no_hp" class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Role -->
          <div class="mb-4">
            <label>Role</label>
            <select name="role" class="w-full border rounded-lg px-3 py-2">

              <option value="admin">Admin</option>
              <option value="pengusul">Pengusul</option>
              <option value="perencanaan">Perencanaan</option>
              <option value="wadir">Wadir</option>
              <option value="spi">SPI</option>
              <option value="direktur">Direktur</option>

            </select>
          </div>

          <!-- Unit -->
          <div class="mb-4">
            <label>Unit</label>

            <select name="unit_id" class="w-full border rounded-lg px-3 py-2">

              @foreach($units as $unit)

              <option value="{{ $unit->id }}">
                {{ $unit->nama_unit }}
              </option>

              @endforeach

            </select>

          </div>

          <!-- Status -->
          <div class="mb-4">
            <label>Status</label>

            <select name="status" class="w-full border rounded-lg px-3 py-2">

              <option value="1">Aktif</option>
              <option value="0">Non Aktif</option>

            </select>

          </div>

          <div class="flex justify-end gap-2">

            <a href="{{ route('users.index') }}"
              class="bg-gray-500 text-white px-4 py-2 rounded-lg">
              Batal
            </a>

            <button type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg">
              Simpan
            </button>

          </div>

        </form>

      </div>
    </div>
  </div>

</x-app-layout>