<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // US-27: pagu anggaran institusi per tahun, untuk grafik pagu vs usulan.
    public function up(): void
    {
        Schema::table('tahuns', function (Blueprint $table) {
            $table->decimal('pagu', 17, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tahuns', function (Blueprint $table) {
            $table->dropColumn('pagu');
        });
    }
};
