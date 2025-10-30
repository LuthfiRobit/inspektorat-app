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
        Schema::create('history_laporan', function (Blueprint $table) {
            $table->increments('id_history_laporan');
            $table->unsignedInteger('laporan_id');
            $table->string('status_sebelum', 20)->nullable();
            $table->string('status_sesudah', 20)->nullable();
            $table->text('catatan_perubahan')->nullable();
            $table->unsignedInteger('changed_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('laporan_id')->references('id_laporan')->on('laporan_kegiatan')->onDelete('cascade');
            $table->foreign('changed_by')->references('id_user')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['laporan_id', 'created_at']);
            $table->index('status_sesudah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_laporan');
    }
};
