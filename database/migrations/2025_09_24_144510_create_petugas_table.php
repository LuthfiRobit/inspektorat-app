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
        Schema::create('petugas', function (Blueprint $table) {
            $table->smallIncrements('id_petugas');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedSmallInteger('kecamatan_id')->nullable()->index();
            $table->unsignedSmallInteger('desa_id')->nullable()->index();
            $table->string('nama_lengkap', 100)->index();
            $table->string('nip', 20)->nullable()->unique()->index();
            $table->string('jabatan', 100)->nullable()->index();
            $table->string('unit_kerja', 100)->nullable();
            $table->string('no_telp', 15)->nullable();
            $table->date('tanggal_awal')->nullable()->index();
            $table->date('tanggal_akhir')->nullable()->index();
            $table->text('alamat')->nullable();
            $table->string('foto_petugas')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id_user')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->foreign('kecamatan_id')
                ->references('id_kecamatan')->on('kecamatan')->onDelete('set null')->onUpdate('cascade');

            $table->foreign('desa_id')
                ->references('id_desa')->on('desa')->onDelete('set null')->onUpdate('cascade');

            // Composite indexes for better performance
            $table->index(['status', 'jabatan']);
            $table->index(['kecamatan_id', 'desa_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
