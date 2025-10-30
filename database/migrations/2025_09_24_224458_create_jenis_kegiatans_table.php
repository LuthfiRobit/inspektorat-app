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
        Schema::create('jenis_kegiatan', function (Blueprint $table) {
            $table->smallIncrements('id_jenis_kegiatan');
            $table->unsignedSmallInteger('tahun_anggaran_id')->index();
            $table->string('kode_jenis', 10)->index();
            $table->string('nama_jenis', 100)->index();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('tahun_anggaran_id')
                ->references('id_tahun_anggaran')->on('tahun_anggaran')->onDelete('restrict')->onUpdate('cascade');

            // Unique constraint for kode_jenis per tahun
            $table->unique(['tahun_anggaran_id', 'kode_jenis']);

            // Composite indexes for better performance
            $table->index(['tahun_anggaran_id', 'status']);
            $table->index(['status', 'kode_jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_kegiatan');
    }
};
