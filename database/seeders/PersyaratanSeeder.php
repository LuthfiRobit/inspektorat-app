<?php

namespace Database\Seeders;

use App\Models\Persyaratan;
use App\Models\PertanyaanKegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersyaratanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pertanyaan1 = PertanyaanKegiatan::first();
        $pertanyaan2 = PertanyaanKegiatan::skip(1)->first();

        if (!$pertanyaan1 || !$pertanyaan2) {
            $this->command->warn('Pertanyaan kegiatan tidak ditemukan. Seeder Persyaratan dilewati.');
            return;
        }

        $data = [
            // Untuk pertanyaan 1
            [
                'pertanyaan_kegiatan_id' => $pertanyaan1->id_pertanyaan,
                'urutan' => 1,
                'nama_persyaratan' => 'Laporan Realisasi Keuangan',
                'template_persyaratan' => 'template/laporan_realisasi.pdf',
                'deskripsi' => 'Format PDF, maksimal 10MB',
                'tipe' => 'wajib',
                'status' => 'active',
            ],
            [
                'pertanyaan_kegiatan_id' => $pertanyaan1->id_pertanyaan,
                'urutan' => 2,
                'nama_persyaratan' => 'Berita Acara Verifikasi',
                'template_persyaratan' => 'template/berita_acara.docx',
                'deskripsi' => 'Sudah ditandatangani',
                'tipe' => 'wajib',
                'status' => 'active',
            ],
            [
                'pertanyaan_kegiatan_id' => $pertanyaan1->id_pertanyaan,
                'urutan' => 3,
                'nama_persyaratan' => 'Dokumen Pendukung Lainnya',
                'template_persyaratan' => null,
                'deskripsi' => 'Dokumen tambahan jika ada',
                'tipe' => 'tambahan',
                'status' => 'active',
            ],

            // Untuk pertanyaan 2
            [
                'pertanyaan_kegiatan_id' => $pertanyaan2->id_pertanyaan,
                'urutan' => 1,
                'nama_persyaratan' => 'Dokumen Pengajuan Siltap',
                'template_persyaratan' => 'template/form_pengajuan_siltap.xls',
                'deskripsi' => 'Formulir pengajuan',
                'tipe' => 'wajib',
                'status' => 'active',
            ],
        ];

        foreach ($data as $item) {
            Persyaratan::updateOrCreate(
                [
                    'pertanyaan_kegiatan_id' => $item['pertanyaan_kegiatan_id'],
                    'nama_persyaratan' => $item['nama_persyaratan'],
                ],
                $item
            );
        }

        $this->command->info('Seeder Persyaratan berhasil dijalankan.');
    }
}
