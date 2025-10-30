<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scoring_desa', function (Blueprint $table) {
            $table->increments('id_scoring');
            $table->unsignedSmallInteger('desa_id');
            $table->unsignedInteger('laporan_id');
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->integer('total_persyaratan_wajib')->default(0);
            $table->integer('persyaratan_terpenuhi')->default(0);
            $table->decimal('persentase_kelengkapan', 5, 2)->default(0);
            $table->integer('skor_ketepatan_waktu')->default(0);
            $table->integer('total_skor')->default(0);
            $table->integer('peringkat')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('desa_id')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('laporan_id')->references('id_laporan')->on('laporan_kegiatan')->onDelete('cascade');

            // Indexes
            $table->index(['desa_id', 'tahun', 'bulan']);
            $table->index(['tahun', 'bulan', 'peringkat']);
            $table->index('total_skor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_desa');
    }
};
