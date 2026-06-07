<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Kelola User
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-700">
            Manajemen Pengguna
            <p class="text-sm text-gray-500">Kelola semua pengguna sistem SIPUA</p>
          </h2>

          <a href="{{ route('users.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded-lg shadow flex items-center gap-2">
            ➕ Tambah User
          </a>
        </div>

        <!-- Search -->
        <div class="mb-4">
          <input type="text" id="searchUser"
            placeholder="Cari user..."
            class="border rounded-lg px-4 py-2 w-full">
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border border-gray-200 rounded-lg">
            <table id="userTable" class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
              <thead class="bg-gray-800 text-white">
                <tr class="text-center">
                  <th class="px-3 py-2 w-12">No</th>
                  <th class="px-4 py-2">Nama</th>
                  <th class="px-6 py-2">Email</th>
                  <!--   <th class="px-6 py-2">Nama Lengkap</th> -->
                  <!--   <th class="px-4 py-2">NIP</th> -->
                  <th class="px-4 py-2">Jabatan</th>
                  <th class="px-4 py-2">No_hp</th>
                  <th class="px-3 py-2">Role</th>
                  <!-- <th class="px-4 py-2">Unit</th> -->
                  <th class="px-3 py-2">Terakhir Login</th>
                  <th class="px-3 py-2">Status</th>
                  <th class="px-4 py-2 w-28">Aksi</th>
                </tr>
              </thead>
              <tbody>

                @foreach($users as $user)

                <tr class="border-b hover:bg-gray-50 text-center">

                  <td class="px-3 py-2">{{ $loop->iteration }}</td>
                  <td class="px-4 py-2">{{ $user->name }}</td>
                  <td class="px-6 py-2">{{ $user->email }}</td>
                  <!--   <td class="px-6 py-2">{{ $user->nama_lengkap ?? '-' }}</td> -->
                  <!-- <td class="px-4 py-2">{{ $user->nip ?? '-' }}</td> -->
                  <td class="px-4 py-2">{{ $user->jabatan ?? '-' }}</td>
                  <td class="px-4 py-2">{{ $user->no_hp ?? '-' }}</td>
                  <td class="px-3 py-2">
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm">
                      {{ $user->role }}
                    </span>
                  </td>
                  <!-- <td class="px-4 py-2">{{ $user->unit_id ?? '-' }}</td>-->
                  <td class="px-4 py-2">{{ $user->updated_at}}</td>
                  <td class="px-3 py-2">

                    @if($user->Status == 1)

                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">
                      Aktif
                    </span>

                    @else

                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm">
                      Non Aktif
                    </span>

                    @endif
                  </td>
                  <td class="px-4 py-2">
                    <div class="flex justify-center gap-2">

                      <a href="{{ route('users.edit', $user->id) }}"
                        class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded">
                        ✏️
                      </a>

                      <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <button
                          class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded"
                          onclick="return confirm('Yakin hapus user ini?')">
                          🗑️
                        </button>

                      </form>

                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </table>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
<script>
  document.getElementById("searchUser").addEventListener("keyup", function() {

    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#userTable tbody tr");

    rows.forEach(function(row) {

      let text = row.textContent.toLowerCase();

      if (text.includes(filter)) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }

    });

  });
</script>