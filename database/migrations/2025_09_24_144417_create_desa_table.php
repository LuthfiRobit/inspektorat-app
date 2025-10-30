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
        Schema::create('desa', function (Blueprint $table) {
            $table->smallIncrements('id_desa');
            $table->unsignedSmallInteger('kecamatan_id')->index();
            $table->string('kode_desa', 10)->unique()->index();
            $table->string('nama_desa', 200)->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('kecamatan_id')->references('id_kecamatan')->on('kecamatan')->onDelete('restrict')->onUpdate('cascade');

            // Composite indexes for better performance
            $table->index(['kecamatan_id', 'status']);
            $table->index(['status', 'nama_desa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
