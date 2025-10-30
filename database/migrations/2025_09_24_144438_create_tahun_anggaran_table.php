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
        Schema::create('tahun_anggaran', function (Blueprint $table) {
            $table->smallIncrements('id_tahun_anggaran');
            $table->year('tahun')->unique()->index();
            $table->enum('status', ['active', 'inactive'])->default('inactive')->index();
            $table->text('keterangan')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Additional indexes for better performance
            $table->index(['status', 'tahun']);
            $table->index(['tahun', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahun_anggaran');
    }
};
