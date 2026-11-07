<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Kelola Tahun Anggaran
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      <div class="bg-white shadow-md rounded-lg p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">

          <div>
            <h3 class="text-lg font-bold">Manajemen Tahun Anggaran</h3>
            <p class="text-sm text-gray-500">Kelola tahun anggaran yang digunakan dalam sistem SIPUA</p>
          </div>

          <a href="{{ route('tahun.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded-lg shadow flex items-center gap-2">
            ➕ Tambah Tahun
          </a>
          </a>

        </div>

        <!-- Search -->
        <div class="mb-4">
          <input type="text"
            id="searchTahun"
            placeholder="Cari tahun..."
            class="border rounded-lg px-4 py-2 w-full">
        </div>


        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border border-gray-200 rounded-lg">
            <table id="tahunTable" class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
              <thead class="bg-gray-800 text-white">
                <tr class="text-center">
                  <th class="px-4 py-2 border">No</th>
                  <th class="py-3 px-4 border">Tahun</th>
                  <th class="py-3 px-4 border">Status</th>
                  <th class="py-3 px-4 border">Aksi</th>
                </tr>
              </thead>

              <tbody>

                @foreach($tahuns as $tahun)

                <tr class="text-center hover:bg-gray-50">

                  <td class="py-2 border">
                    {{ $loop->iteration }}
                  </td>

                  <td class="py-2 border font-medium">

                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm">
                      {{ $tahun->tahun }}
                    </span>

                  </td>

                  <td class="py-2 border">

                    @if($tahun->status == 1)

                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">
                      Aktif
                    </span>

                    @else

                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-sm">
                      Tidak Aktif
                    </span>

                    @endif

                  </td>

                  <td class="py-2 border">

                    <div class="flex justify-center gap-2">

                      <a href="{{ route('tahun.edit',$tahun->id) }}"
                        class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                        ✏️
                      </a>

                      <form action="{{ route('tahun.destroy',$tahun->id) }}" method="POST">

                        @csrf
                        @method('DELETE')

                        <button
                          onclick="return confirm('Yakin hapus tahun ini?')"
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
  document.getElementById("searchTahun").addEventListener("keyup", function() {

    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tahunTable tbody tr");

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