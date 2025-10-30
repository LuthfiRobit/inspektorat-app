<?php

namespace Database\Seeders;

use App\Models\LaporanKegiatan;
use App\Models\PertanyaanKegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JawabanPertanyaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua laporan kegiatan
        $laporanList = DB::table('laporan_kegiatan')->get();

        if ($laporanList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada laporan_kegiatan ditemukan, seeder jawaban_pertanyaan dibatalkan.');
            return;
        }

        foreach ($laporanList as $laporan) {
            // Ambil pertanyaan kegiatan yang sesuai dengan kegiatan_id pada laporan
            $pertanyaanList = DB::table('pertanyaan_kegiatan')
                ->where('kegiatan_id', $laporan->kegiatan_id)
                ->get();

            foreach ($pertanyaanList as $pertanyaan) {
                // Cek jika jawaban sudah ada supaya tidak duplikat
                $exists = DB::table('jawaban_pertanyaan')
                    ->where('laporan_id', $laporan->id_laporan)
                    ->where('pertanyaan_id', $pertanyaan->id_pertanyaan)
                    ->exists();

                if (!$exists) {
                    // Generate contoh jawaban text
                    $jawabanText = 'Jawaban untuk pertanyaan "' . substr($pertanyaan->pertanyaan, 0, 30) . '" pada laporan #' . $laporan->id_laporan;

                    // Status acak antara draft dan submitted
                    $status = rand(0, 1) ? 'draft' : 'submitted';

                    DB::table('jawaban_pertanyaan')->insert([
                        'laporan_id' => $laporan->id_laporan,
                        'pertanyaan_id' => $pertanyaan->id_pertanyaan,
                        'jawaban_text' => $jawabanText,
                        'status' => $status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('✅ Jawaban pertanyaan untuk laporan kegiatan berhasil disimulasikan.');
    }
}
