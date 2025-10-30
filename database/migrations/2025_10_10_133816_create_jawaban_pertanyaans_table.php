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
        Schema::create('jawaban_pertanyaan', function (Blueprint $table) {
            $table->increments('id_jawaban');
            $table->unsignedInteger('laporan_id');
            $table->unsignedSmallInteger('pertanyaan_id');
            $table->text('jawaban_text')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->timestamps();

            // Foreign keys
            $table->foreign('laporan_id')->references('id_laporan')->on('laporan_kegiatan')->onDelete('cascade');
            $table->foreign('pertanyaan_id')->references('id_pertanyaan')->on('pertanyaan_kegiatan')->onDelete('cascade');

            // Unique constraint - satu jawaban per pertanyaan per laporan
            $table->unique(['laporan_id', 'pertanyaan_id']);

            // Indexes
            $table->index(['laporan_id', 'status']);
            $table->index('pertanyaan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_pertanyaan');
    }
};
