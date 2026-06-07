<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edit User
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto">

      <div class="bg-white shadow-md rounded-lg p-6">

        <form action="{{ route('users.update',$user->id) }}" method="POST">

          @csrf
          @method('PUT')

          <!-- Username -->
          <div class="mb-4">
            <label>Username</label>
            <input type="text" name="name"
              value="{{ $user->name }}"
              class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label>Email</label>
            <input type="email" name="email"
              value="{{ $user->email }}"
              class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Nama Lengkap -->
          <div class="mb-4">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap"
              value="{{ $user->nama_lengkap }}"
              class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- NIP -->
          <div class="mb-4">
            <label>NIP</label>
            <input type="text" name="nip"
              value="{{ $user->nip }}"
              class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Jabatan -->
          <div class="mb-4">
            <label>Jabatan</label>
            <input type="text" name="jabatan"
              value="{{ $user->jabatan }}"
              class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- No HP -->
          <div class="mb-4">
            <label>No HP</label>
            <input type="text" name="no_hp"
              value="{{ $user->no_hp }}"
              class="w-full border rounded-lg px-3 py-2">
          </div>

          <!-- Role -->
          <div class="mb-4">
            <label>Role</label>

            <select name="role" class="w-full border rounded-lg px-3 py-2">

              <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
              <option value="pengusul" {{ $user->role == 'pengusul' ? 'selected' : '' }}>Pengusul</option>
              <option value="perencanaan" {{ $user->role == 'perencanaan' ? 'selected' : '' }}>Perencanaan</option>
              <option value="wadir" {{ $user->role == 'wadir' ? 'selected' : '' }}>Wadir</option>
              <option value="spi" {{ $user->role == 'spi' ? 'selected' : '' }}>SPI</option>
              <option value="direktur" {{ $user->role == 'direktur' ? 'selected' : '' }}>Direktur</option>

            </select>

          </div>

          <!-- Unit -->
          <div class="mb-4">
            <label>Unit</label>

            <select name="unit_id" class="w-full border rounded-lg px-3 py-2">

              @foreach($units as $unit)

              <option value="{{ $unit->id }}"
                {{ $user->unit_id == $unit->id ? 'selected' : '' }}>

                {{ $unit->nama_unit }}

              </option>

              @endforeach

            </select>

          </div>

          <!-- Status -->
          <div class="mb-4">
            <label>Status</label>

            <select name="Status" class="w-full border rounded-lg px-3 py-2">

              <option value="1" {{ $user->Status == 1 ? 'selected' : '' }}>Aktif</option>
              <option value="0" {{ $user->Status == 0 ? 'selected' : '' }}>Non Aktif</option>

            </select>

          </div>

          <div class="flex justify-end gap-2">

            <a href="{{ route('users.index') }}"
              class="bg-gray-500 text-white px-4 py-2 rounded-lg">
              Batal
            </a>

            <button type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg">
              Update
            </button>

          </div>

        </form>

      </div>
    </div>
  </div>

</x-app-layout>