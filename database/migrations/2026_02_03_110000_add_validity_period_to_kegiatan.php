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
            $table->tinyInteger('bulan_mulai')->nullable()->after('frekuensi_pelaporan')->comment('Start Month for Recurring');
            $table->tinyInteger('bulan_selesai')->nullable()->after('bulan_mulai')->comment('End Month for Recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn(['bulan_mulai', 'bulan_selesai']);
        });
    }
};
