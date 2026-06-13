<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite index for the most frequent query pattern in getListForUser().
     * Pattern: WHERE desa_id IN (...) AND kegiatan_id IN (...)
     * This dramatically speeds up the batch lookup query.
     */
    public function up(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            // Composite index for the batch lookup pattern:
            // SELECT * FROM laporan_kegiatan WHERE desa_id IN (...) AND kegiatan_id IN (...)
            $table->index(['desa_id', 'kegiatan_id', 'tahun', 'bulan'], 'laporan_lookup_composite_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_kegiatan', function (Blueprint $table) {
            $table->dropIndex('laporan_lookup_composite_idx');
        });
    }
};
