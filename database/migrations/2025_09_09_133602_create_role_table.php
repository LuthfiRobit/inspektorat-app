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
        Schema::create('role', function (Blueprint $table) {
            $table->smallIncrements('id_role'); // smallIncrements for smaller ID range
            $table->enum('role_scope', ['inspektorat', 'kecamatan', 'desa'])->nullable();
            $table->string('role_name')->unique(); // More descriptive name
            $table->string('role_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
