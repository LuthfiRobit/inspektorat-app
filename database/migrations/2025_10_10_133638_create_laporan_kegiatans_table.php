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
        Schema::create('laporan_kegiatan', function (Blueprint $table) {
            $table->increments('id_laporan');
            $table->unsignedSmallInteger('desa_id');
            $table->unsignedSmallInteger('kegiatan_id');
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->enum('status', ['draft', 'submitted', 'revision', 'approved', 'rejected'])->default('draft');
            $table->date('tanggal_target');
            $table->timestamp('tanggal_submit')->nullable();
            $table->timestamp('tanggal_approve')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('desa_id')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('kegiatan_id')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');
            $table->foreign('approved_by')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id_user')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['desa_id', 'tahun', 'bulan']);
            $table->index(['kegiatan_id', 'status']);
            $table->index(['status', 'tanggal_target']);
            $table->index(['tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kegiatan');
    }
};
