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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->smallIncrements('id_kegiatan');
            $table->unsignedSmallInteger('tahun_anggaran_id')->index();
            $table->unsignedSmallInteger('jenis_kegiatan_id')->index();
            $table->string('kode_kegiatan', 20)->index();
            $table->text('nama_kegiatan');
            $table->tinyInteger('bulan')->nullable()->comment('Bulan pelaksanaan (1-12)')->index();
            $table->tinyInteger('tanggal_mulai')->nullable()->comment('Tanggal mulai (1-31)');
            $table->tinyInteger('tanggal_selesai')->nullable()->comment('Tanggal selesai (1-31)');
            $table->tinyInteger('batas_akhir_upload')->nullable()->comment('Batas akhir upload (hari setelah tanggal selesai)');
            $table->text('dasar_hukum')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('tahun_anggaran_id')
                ->references('id_tahun_anggaran')->on('tahun_anggaran')->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('jenis_kegiatan_id')
                ->references('id_jenis_kegiatan')->on('jenis_kegiatan')->onDelete('restrict')->onUpdate('cascade');

            // Unique constraint for kode_kegiatan per tahun
            $table->unique(['tahun_anggaran_id', 'kode_kegiatan']);

            // Composite indexes for better performance
            $table->index(['tahun_anggaran_id', 'jenis_kegiatan_id']);
            $table->index(['jenis_kegiatan_id', 'status']);
            $table->index(['bulan', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
