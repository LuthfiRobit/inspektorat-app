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
        Schema::create('log_activity', function (Blueprint $table) {
            $table->integer('id_log_activity')->autoIncrement()->unsigned();
            $table->integer('user_id')->nullable(); // Bisa null jika aktivitas dilakukan oleh sistem
            $table->string('action'); // Jenis aktivitas (create, update, delete, dll)
            $table->text('description')->nullable(); // Deskripsi aktivitas
            $table->string('ip_address')->nullable(); // IP pengguna
            $table->string('user_agent')->nullable(); // Info browser & device
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activity');
    }
};
