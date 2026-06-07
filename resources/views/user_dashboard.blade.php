<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overﬂow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          {{ __("Anda berhasil login, anda login sebagai user baru belum memiliki fitur seharusnya silakan menghubungi admin perencanaan untuk mengaktifkan akun anda atau hubungi wa 0812-2649-9000! terima kasih") }}
        </div>
      </div>
    </div>
  </div>
</x-app-layout>