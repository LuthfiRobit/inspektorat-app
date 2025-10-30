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
        Schema::create('pertanyaan_kegiatan', function (Blueprint $table) {
            $table->smallIncrements('id_pertanyaan');
            $table->unsignedSmallInteger('kegiatan_id')->index();
            $table->integer('urutan')->default(1)->index();
            $table->text('pertanyaan');
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('kegiatan_id')
                ->references('id_kegiatan')->on('kegiatan')->onDelete('cascade')->onUpdate('cascade');

            // Composite indexes for better performance
            $table->index(['kegiatan_id', 'urutan']);
            $table->index(['kegiatan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_kegiatan');
    }
};
