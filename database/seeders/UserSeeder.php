<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Satu akun demo per peran. Password semua akun: "password".
 * Hanya untuk development — jangan dijalankan di server produksi.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $unit = fn (string $kode) => Unit::where('unit_id', $kode)->value('id');

        $users = [
            ['admin', 'Administrator', 'admin@sipua.test', 'PRC'],
            ['pengusul', 'Pengusul Teknik Informatika', 'pengusul@sipua.test', 'TI'],
            ['perencanaan', 'Staf Perencanaan', 'perencanaan@sipua.test', 'PRC'],
            ['wadir', 'Wakil Direktur', 'wadir@sipua.test', null],
            ['spi', 'Auditor SPI', 'spi@sipua.test', 'SPI'],
            ['direktur', 'Direktur', 'direktur@sipua.test', null],
        ];

        foreach ($users as [$role, $name, $email, $kodeUnit]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'unit_id' => $kodeUnit ? $unit($kodeUnit) : null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
