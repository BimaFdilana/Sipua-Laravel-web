<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('tahun_id')->constrained('tahuns')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // pembuat

            // Langkah 1 — informasi kegiatan
            $table->string('program')->nullable();
            $table->string('hasil')->nullable();
            $table->string('judul_kegiatan');
            $table->string('jenis_output')->nullable();
            $table->decimal('volume_output', 12, 2)->nullable();
            $table->string('satuan_output')->nullable();
            $table->date('waktu_mulai')->nullable();
            $table->date('waktu_selesai')->nullable();

            // Langkah 2 — KAK/TOR
            $table->text('dasar_hukum')->nullable();
            $table->text('latar_belakang')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('penerima_manfaat')->nullable();
            $table->text('metode_pelaksanaan')->nullable();
            $table->text('uraian_kebutuhan')->nullable();

            $table->decimal('total_rab', 15, 2)->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->string('prioritas', 10)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'tahun_id']);
        });

        Schema::create('rab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usulan_id')->constrained('usulans')->cascadeOnDelete();
            $table->string('kode_akun');
            $table->foreign('kode_akun')->references('kode_akun')->on('akun_belanjas')->restrictOnDelete();
            $table->string('uraian');
            $table->decimal('volume', 12, 2);
            $table->string('satuan', 50);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('usulan_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usulan_id')->constrained('usulans')->cascadeOnDelete();
            $table->string('kategori', 50);
            $table->string('nama_asli');
            $table->string('path');
            $table->unsignedInteger('ukuran'); // byte
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('iku_usulan', function (Blueprint $table) {
            $table->foreignId('usulan_id')->constrained('usulans')->cascadeOnDelete();
            $table->foreignId('iku_id')->constrained('ikus')->restrictOnDelete();
            $table->primary(['usulan_id', 'iku_id']);
        });

        Schema::create('usulan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usulan_id')->constrained('usulans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('tahap', 20);   // pengusul | perencanaan | wadir | spi | direktur
            $table->string('aksi', 20);    // buat | kirim | teruskan | kembalikan | tolak | sahkan | prioritas
            $table->string('status_dari', 30)->nullable();
            $table->string('status_ke', 30);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_logs');
        Schema::dropIfExists('iku_usulan');
        Schema::dropIfExists('usulan_dokumens');
        Schema::dropIfExists('rab_items');
        Schema::dropIfExists('usulans');
    }
};
