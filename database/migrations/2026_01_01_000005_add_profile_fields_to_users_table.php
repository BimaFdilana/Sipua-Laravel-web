<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // admin | pengusul | perencanaan | wadir | spi | direktur
            $table->string('role')->default('pengusul')->after('password');
            $table->foreignId('unit_id')->nullable()->after('role')->constrained('units')->nullOnDelete();
            $table->string('nama_lengkap')->nullable();
            $table->string('nip')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('no_hp')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn(['role', 'nama_lengkap', 'nip', 'jabatan', 'no_hp', 'is_active']);
        });
    }
};
