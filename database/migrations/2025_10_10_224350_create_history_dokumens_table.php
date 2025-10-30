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
        Schema::create('history_dokumen', function (Blueprint $table) {
            $table->increments('id_history_dokumen');
            $table->unsignedInteger('dokumen_id');
            $table->enum('action', ['upload', 'revision', 'approve', 'reject']);
            $table->string('nama_file_sebelum', 255)->nullable();
            $table->string('path_file_sebelum', 500)->nullable();
            $table->text('catatan_perubahan')->nullable();
            $table->unsignedInteger('changed_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('dokumen_id')->references('id_dokumen')->on('dokumen_persyaratan')->onDelete('cascade');
            $table->foreign('changed_by')->references('id_user')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['dokumen_id', 'action']);
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_dokumen');
    }
};
