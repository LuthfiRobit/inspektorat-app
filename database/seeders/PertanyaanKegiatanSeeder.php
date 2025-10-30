<?php

namespace Database\Seeders;

use App\Models\PertanyaanKegiatan;
use App\Models\Kegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PertanyaanKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample kegiatan (asumsi KEG001 = kegiatan_id 1, KEG004 = kegiatan_id 2)
        $kegiatan1 = Kegiatan::where('kode_kegiatan', 'KEG001')->first();
        $kegiatan2 = Kegiatan::where('kode_kegiatan', 'KEG004')->first();

        if (!$kegiatan1 || !$kegiatan2) {
            // Fallback jika tidak ditemukan
            $kegiatan1 = Kegiatan::first();
            $kegiatan2 = Kegiatan::skip(1)->first();
        }

        $data = [
            // Untuk kegiatan 1 (Laporan Realisasi APBDes)
            [
                'kegiatan_id' => $kegiatan1->id_kegiatan,
                'urutan' => 1,
                'pertanyaan' => 'Apakah laporan realisasi keuangan sudah sesuai dengan format?',
                'status' => 'active',
            ],
            [
                'kegiatan_id' => $kegiatan1->id_kegiatan,
                'urutan' => 2,
                'pertanyaan' => 'Apakah semua dokumen pendukung telah dilampirkan?',
                'status' => 'active',
            ],

            // Untuk kegiatan 2 (Proses Siltap)
            [
                'kegiatan_id' => $kegiatan2->id_kegiatan,
                'urutan' => 1,
                'pertanyaan' => 'Apakah dokumen pengajuan siltap sudah lengkap?',
                'status' => 'active',
            ],
        ];

        foreach ($data as $item) {
            PertanyaanKegiatan::firstOrCreate(
                [
                    'kegiatan_id' => $item['kegiatan_id'],
                    'pertanyaan' => $item['pertanyaan']
                ],
                $item
            );
        }
    }
}
