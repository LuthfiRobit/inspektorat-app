<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('petugas_wilayah_binaan', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('petugas_id')->index();
            $table->unsignedSmallInteger('kecamatan_id')->nullable()->index();
            $table->unsignedSmallInteger('desa_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('petugas_id')->references('id_petugas')->on('petugas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kecamatan_id')->references('id_kecamatan')->on('kecamatan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('desa_id')->references('id_desa')->on('desa')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id_user')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id_user')->on('users')->onDelete('set null')->onUpdate('cascade');

            // Unique constraints
            // One kecamatan can only be supervised by one petugas inspektorat
            $table->unique('kecamatan_id', 'unique_kecamatan');
            // One desa can only be supervised by one petugas kecamatan
            $table->unique('desa_id', 'unique_desa');
        });

        // // Seed permissions
        // DB::table('permission')->insert([
        //     [
        //         'permission_name' => 'master.wilayah-binaan.view',
        //         'permission_description' => 'Melihat daftar wilayah binaan',
        //         'is_active' => true,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'permission_name' => 'master.wilayah-binaan.create',
        //         'permission_description' => 'Mengelola/menambahkan wilayah binaan',
        //         'is_active' => true,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'permission_name' => 'master.wilayah-binaan.delete',
        //         'permission_description' => 'Menghapus wilayah binaan',
        //         'is_active' => true,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permission')->whereIn('permission_name', [
            'master.wilayah-binaan.view',
            'master.wilayah-binaan.create',
            'master.wilayah-binaan.delete'
        ])->delete();
        Schema::dropIfExists('petugas_wilayah_binaan');
    }
};
