<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = "
            CREATE OR REPLACE VIEW v_target_pelaporan AS
            WITH RECURSIVE months AS (
                SELECT 1 as month_num
                UNION ALL
                SELECT month_num + 1 FROM months WHERE month_num < 12
            )
            SELECT 
                CONCAT(d.id_desa, '_', k.id_kegiatan, '_', m.month_num, '_', ta.tahun) as id,
                d.id_desa as desa_id,
                d.nama_desa,
                d.kode_desa,
                d.kecamatan_id,
                kec.nama_kecamatan,
                k.id_kegiatan as kegiatan_id,
                k.nama_kegiatan,
                k.kode_kegiatan,
                k.tanggal_mulai,
                k.tanggal_selesai,
                k.batas_akhir_upload,
                k.frekuensi_pelaporan,
                k.bulan_mulai,
                k.bulan_selesai,
                ta.id_tahun_anggaran as tahun_anggaran_id,
                ta.tahun as tahun_anggaran,
                ta.tahun as tahun,
                jk.nama_jenis as jenis_kegiatan,
                m.month_num as bulan,
                lk.id_laporan,
                COALESCE(lk.status, 'belum_dilaporkan') as status,
                lk.tanggal_submit,
                lk.tanggal_approve,
                lk.tanggal_target as db_tanggal_target
            FROM desa d
            CROSS JOIN kegiatan k
            LEFT JOIN kecamatan kec ON d.kecamatan_id = kec.id_kecamatan
            LEFT JOIN jenis_kegiatan jk ON k.jenis_kegiatan_id = jk.id_jenis_kegiatan
            JOIN tahun_anggaran ta ON k.tahun_anggaran_id = ta.id_tahun_anggaran
            JOIN months m ON (
                (k.frekuensi_pelaporan IS NULL AND m.month_num = k.bulan) 
                OR 
                (k.frekuensi_pelaporan IS NOT NULL AND 
                 m.month_num >= COALESCE(k.bulan_mulai, 1) AND 
                 m.month_num <= COALESCE(k.bulan_selesai, 12) AND 
                 (m.month_num - COALESCE(k.bulan_mulai, 1)) % k.frekuensi_pelaporan = 0)
            )
            LEFT JOIN laporan_kegiatan lk ON lk.desa_id = d.id_desa 
                                          AND lk.kegiatan_id = k.id_kegiatan 
                                          AND lk.bulan = m.month_num
                                          AND lk.tahun = ta.tahun
            WHERE d.status = 'active' 
              AND k.status = 'active'
              AND (jk.status = 'active' OR k.jenis_kegiatan_id IS NULL);
        ";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_target_pelaporan");
    }
};
