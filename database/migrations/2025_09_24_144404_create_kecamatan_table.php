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
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->smallIncrements('id_kecamatan');
            $table->string('kode_kecamatan', 10)->unique()->index();
            $table->string('nama_kecamatan', 100)->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Additional indexes for better performance
            $table->index(['status', 'nama_kecamatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
