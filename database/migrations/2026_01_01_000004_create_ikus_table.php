<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ikus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_iku')->unique();
            $table->string('nama_iku');
            $table->enum('sifat', ['Wajib', 'Pilihan']);
            $table->year('tahun');
            $table->string('target')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('kriteria')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ikus');
    }
};
