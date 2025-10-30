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
        Schema::create('keterlambatan', function (Blueprint $table) {
            $table->increments('id_keterlambatan');
            $table->unsignedInteger('laporan_id');
            $table->unsignedSmallInteger('desa_id');
            $table->date('tanggal_target');
            $table->date('tanggal_upload');
            $table->integer('hari_keterlambatan');
            $table->timestamps();

            // Foreign keys
            $table->foreign('laporan_id')->references('id_laporan')->on('laporan_kegiatan')->onDelete('cascade');
            $table->foreign('desa_id')->references('id_desa')->on('desa')->onDelete('cascade');

            // Indexes
            $table->index(['desa_id', 'tanggal_target']);
            $table->index('hari_keterlambatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keterlambatan');
    }
};
