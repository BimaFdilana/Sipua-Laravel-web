<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Kelola Akun Belanja
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">

          <div>
            <h3 class="text-lg font-bold">Master Akun Belanja</h3>
            <p class="text-sm text-gray-500">Kelola data akun belanja SIPUA</p>
          </div>

          <a href="{{ route('akun-belanja.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded-lg shadow flex items-center gap-2">
            ➕ Tambah Akun
          </a>

        </div>

        <!-- Search -->
        <div class="mb-4">
          <input type="text" id="searchAkun"
            placeholder="Cari akun belanja..."
            class="border rounded-lg px-4 py-2 w-full">
        </div>

        <div class="overflow-x-auto">

          <table id="akunTable" class="min-w-full border border-gray-200 rounded-lg overflow-hidden">

            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="py-3 px-4 border">No</th>
                <th class="py-3 px-4 border">Kode Akun</th>
                <th class="py-3 px-4 border">Nama Akun</th>
                <th class="py-3 px-4 border">Jenis Belanja</th>
                <th class="py-3 px-4 border">Aksi</th>
              </tr>
            </thead>

            <tbody>

              @foreach($akun as $item)

              <tr class="text-center hover:bg-gray-50">

                <td class="py-2 border">{{ $loop->iteration }}</td>

                <td class="py-2 border font-semibold text-blue-600">
                  {{ $item->kode_akun }}
                </td>

                <td class="py-2 border">
                  {{ $item->nama_akun }}
                </td>

                <td class="py-2 border">
                  <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">
                    {{ $item->jenis_belanja ?? '-' }}
                  </span>
                </td>

                <td class="py-2 border">

                  <div class="flex justify-center gap-2">

                    <a href="{{ route('akun-belanja.edit',$item->kode_akun) }}"
                      class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                      ✏️
                    </a>

                    <form action="{{ route('akun-belanja.destroy',$item->kode_akun) }}" method="POST">
                      @csrf
                      @method('DELETE')

                      <button onclick="return confirm('Yakin hapus akun ini?')"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        🗑
                      </button>

                    </form>

                  </div>

                </td>

              </tr>

              @endforeach

            </tbody>

          </table>

        </div>

      </div>
    </div>
  </div>

</x-app-layout>

<script>
  document.getElementById("searchAkun").addEventListener("keyup", function() {

    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#akunTable tbody tr");

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