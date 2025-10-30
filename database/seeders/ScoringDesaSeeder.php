<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoringDesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laporanList = DB::table('laporan_kegiatan')->get();

        if ($laporanList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada data laporan_kegiatan ditemukan.');
            return;
        }

        foreach ($laporanList as $laporan) {
            $kegiatanId = $laporan->kegiatan_id;

            // Hitung total persyaratan wajib dari kegiatan -> pertanyaan -> persyaratan
            $totalPersyaratan = DB::table('pertanyaan_kegiatan')
                ->join('persyaratan', 'pertanyaan_kegiatan.id_pertanyaan', '=', 'persyaratan.pertanyaan_kegiatan_id')
                ->where('pertanyaan_kegiatan.kegiatan_id', $kegiatanId)
                ->count();

            // Hitung dokumen yang berstatus approved untuk laporan ini
            $terpenuhi = DB::table('jawaban_pertanyaan')
                ->join('dokumen_persyaratan', 'jawaban_pertanyaan.id_jawaban', '=', 'dokumen_persyaratan.jawaban_id')
                ->where('jawaban_pertanyaan.laporan_id', $laporan->id_laporan)
                ->where('dokumen_persyaratan.status', 'approved')
                ->where('dokumen_persyaratan.is_current', true)
                ->count();

            $persentase = $totalPersyaratan > 0
                ? round(($terpenuhi / $totalPersyaratan) * 100, 2)
                : 0.00;

            // Ambil keterlambatan jika ada
            $keterlambatan = DB::table('keterlambatan')
                ->where('laporan_id', $laporan->id_laporan)
                ->value('hari_keterlambatan');

            // Skor ketepatan waktu
            $skorWaktu = 0;
            if ($keterlambatan === null) {
                $skorWaktu = 50; // Tidak telat
            } elseif ($keterlambatan <= 3) {
                $skorWaktu = 40;
            } elseif ($keterlambatan <= 7) {
                $skorWaktu = 30;
            } elseif ($keterlambatan <= 14) {
                $skorWaktu = 20;
            } else {
                $skorWaktu = 10;
            }

            $totalSkor = $skorWaktu + intval($persentase); // Sederhana: total = kelengkapan + ketepatan

            // Insert ke scoring_desa
            DB::table('scoring_desa')->insert([
                'desa_id'                 => $laporan->desa_id,
                'laporan_id'             => $laporan->id_laporan,
                'tahun'                  => $laporan->tahun,
                'bulan'                  => $laporan->bulan,
                'total_persyaratan_wajib' => $totalPersyaratan,
                'persyaratan_terpenuhi'  => $terpenuhi,
                'persentase_kelengkapan' => $persentase,
                'skor_ketepatan_waktu'   => $skorWaktu,
                'total_skor'             => $totalSkor,
                'peringkat'              => null, // Nanti bisa diurutkan berdasarkan skor
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        $this->command->info('✅ Seeder scoring_desa selesai.');
    }
}
