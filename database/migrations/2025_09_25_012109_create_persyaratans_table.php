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
        Schema::create('persyaratan', function (Blueprint $table) {
            $table->smallIncrements('id_persyaratan');
            $table->unsignedSmallInteger('pertanyaan_kegiatan_id')->index();
            $table->integer('urutan')->default(1)->index();
            $table->string('nama_persyaratan', 200)->index();
            $table->string('template_persyaratan', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['wajib', 'tambahan'])->default('wajib')->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('pertanyaan_kegiatan_id')
                ->references('id_pertanyaan')->on('pertanyaan_kegiatan')->onDelete('cascade')->onUpdate('cascade');

            // Composite indexes for better performance
            $table->index(['pertanyaan_kegiatan_id', 'urutan']);
            $table->index(['pertanyaan_kegiatan_id', 'tipe']);
            $table->index(['pertanyaan_kegiatan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persyaratan');
    }
};
