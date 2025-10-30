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
        Schema::create('dokumen_persyaratan', function (Blueprint $table) {
            $table->increments('id_dokumen');
            $table->unsignedInteger('jawaban_id');
            $table->unsignedSmallInteger('persyaratan_id');
            $table->string('nama_file', 255);
            $table->string('path_file', 500);
            $table->enum('status', ['draft', 'submitted', 'revision', 'approved'])->default('draft');
            $table->text('catatan_revisi')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('jawaban_id')->references('id_jawaban')->on('jawaban_pertanyaan')->onDelete('cascade');
            $table->foreign('persyaratan_id')->references('id_persyaratan')->on('persyaratan')->onDelete('cascade');
            $table->foreign('created_by')->references('id_user')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['jawaban_id', 'persyaratan_id']);
            $table->index(['status', 'is_current']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_persyaratan');
    }
};
