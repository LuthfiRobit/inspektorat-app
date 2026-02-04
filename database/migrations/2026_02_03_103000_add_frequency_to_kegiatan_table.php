<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->tinyInteger('frekuensi_pelaporan')
                ->nullable()
                ->after('bulan')
                ->comment('1=Bulanan, 2=2Bulan, 3=Triwulan, 6=Semester, NULL=Insidentil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('frekuensi_pelaporan');
        });
    }
};
