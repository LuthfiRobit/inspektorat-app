<?php

namespace Database\Seeders;

use App\Models\JenisKegiatan;
use App\Models\TahunAnggaran;
use Illuminate\Database\Seeder;

class JenisKegiatanSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil tahun anggaran aktif
        $tahunAktif = TahunAnggaran::where('status', 'active')->first();

        // Jika tidak ada, ambil tahun terbaru
        if (!$tahunAktif) {
            $tahunAktif = TahunAnggaran::orderByDesc('tahun')->first();
        }

        // Jika masih tidak ada, buat tahun baru
        if (!$tahunAktif) {
            $tahunAktif = TahunAnggaran::create([
                'tahun' => date('Y'),
                'keterangan' => 'Tahun anggaran untuk seeder',
                'status' => 'active',
            ]);
        }

        // Data Jenis Kegiatan
        $data = [
            [
                'tahun_anggaran_id' => $tahunAktif->id_tahun_anggaran,
                'kode_jenis' => 'JKEG' . $tahunAktif->tahun . 'A',
                'nama_jenis' => 'PERTANGGUNGJAWABAN TAHUN SEBELUMNYA',
                'keterangan' => 'Kegiatan terkait pertanggungjawaban tahun sebelumnya',
                'status' => 'active',
            ],
            [
                'tahun_anggaran_id' => $tahunAktif->id_tahun_anggaran,
                'kode_jenis' => 'JKEG' . $tahunAktif->tahun . 'B',
                'nama_jenis' => 'TAHUN BERJALAN',
                'keterangan' => 'Kegiatan untuk tahun berjalan',
                'status' => 'active',
            ],
            [
                'tahun_anggaran_id' => $tahunAktif->id_tahun_anggaran,
                'kode_jenis' => 'JKEG' . $tahunAktif->tahun . 'C',
                'nama_jenis' => 'UNTUK TAHUN BERIKUTNYA',
                'keterangan' => 'Kegiatan persiapan untuk tahun berikutnya',
                'status' => 'active',
            ],
        ];

        foreach ($data as $item) {
            JenisKegiatan::updateOrCreate(
                [
                    'tahun_anggaran_id' => $item['tahun_anggaran_id'],
                    'kode_jenis' => $item['kode_jenis'],
                ],
                $item
            );
        }
    }
}
