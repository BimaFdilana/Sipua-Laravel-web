<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // US-01: jenis unit kerja (Jurusan / Bagian / Unit) untuk hierarki pengusul.
        Schema::table('units', function (Blueprint $table) {
            $table->string('jenis', 20)->default('Unit')->after('nama_unit');
        });

        DB::table('units')->whereIn('keterangan', ['Jurusan', 'Bagian', 'Unit'])
            ->update(['jenis' => DB::raw('keterangan'), 'keterangan' => null]);

        // US-04: kode IKU unik per tahun, supaya IKU yang sama bisa dipakai lagi di tahun berikutnya.
        Schema::table('ikus', function (Blueprint $table) {
            $table->dropUnique(['kode_iku']);
            $table->unique(['kode_iku', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::table('ikus', function (Blueprint $table) {
            $table->dropUnique(['kode_iku', 'tahun']);
            $table->unique('kode_iku');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
