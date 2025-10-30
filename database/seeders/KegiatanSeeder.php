<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\TahunAnggaran;
use App\Models\JenisKegiatan;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil tahun aktif atau terbaru
        $tahunAktif = TahunAnggaran::where('status', 'active')->first()
            ?? TahunAnggaran::orderByDesc('tahun')->first();

        if (!$tahunAktif) {
            $this->command->warn("⚠️ Tidak ada tahun anggaran aktif. Jalankan TahunAnggaranSeeder dulu.");
            return;
        }

        // Ambil jenis kegiatan berdasarkan kode tahun aktif
        $jenisA = JenisKegiatan::where('kode_jenis', 'JKEG' . $tahunAktif->tahun . 'A')->first();
        $jenisB = JenisKegiatan::where('kode_jenis', 'JKEG' . $tahunAktif->tahun . 'B')->first();
        $jenisC = JenisKegiatan::where('kode_jenis', 'JKEG' . $tahunAktif->tahun . 'C')->first();

        if (!$jenisA || !$jenisB || !$jenisC) {
            $this->command->warn("⚠️ Jenis kegiatan belum tersedia. Jalankan JenisKegiatanSeeder dulu.");
            return;
        }

        $data = [
            // Jenis A (Tahun Sebelumnya)
            [
                'kode_kegiatan' => 'KEG001',
                'nama_kegiatan' => 'Laporan Realisasi Pelaksanaan APBDes Semester 2 (LRP-APBDes Semester 2)',
                'bulan' => 1,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 15,
                'batas_akhir_upload' => 20,
                'dasar_hukum' => 'Peraturan Bupati No. 12 Tahun 2020 tentang Pedoman Penyusunan APBDes',
                'jenis_kegiatan_id' => $jenisA->id_jenis_kegiatan,
            ],
            [
                'kode_kegiatan' => 'KEG002',
                'nama_kegiatan' => 'Laporan Pertanggungjawaban Realisasi Pelaksanaan APBDes (LPRP-APBDes)',
                'bulan' => 2,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 25,
                'batas_akhir_upload' => 28,
                'dasar_hukum' => 'Permendagri No. 20 Tahun 2018 tentang Pengelolaan Keuangan Desa',
                'jenis_kegiatan_id' => $jenisA->id_jenis_kegiatan,
            ],
            [
                'kode_kegiatan' => 'KEG003',
                'nama_kegiatan' => 'Laporan Keterangan Pertanggungjawaban (LKPRP-APBDes)',
                'bulan' => 3,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 30,
                'batas_akhir_upload' => 31,
                'dasar_hukum' => 'UU No. 6 Tahun 2014 tentang Desa',
                'jenis_kegiatan_id' => $jenisA->id_jenis_kegiatan,
            ],

            // Jenis B (Tahun Berjalan)
            [
                'kode_kegiatan' => 'KEG004',
                'nama_kegiatan' => 'Proses Pengajuan sampai Penerimaan Siltap',
                'bulan' => 4,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 15,
                'batas_akhir_upload' => 20,
                'dasar_hukum' => 'Peraturan Bupati tentang Penggajian Pegawai Desa',
                'jenis_kegiatan_id' => $jenisB->id_jenis_kegiatan,
            ],
            [
                'kode_kegiatan' => 'KEG005',
                'nama_kegiatan' => 'Monitoring Pelaksanaan Pembangunan Desa',
                'bulan' => 6,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 25,
                'batas_akhir_upload' => 30,
                'dasar_hukum' => 'Peraturan Desa tentang Pelaksanaan Pembangunan',
                'jenis_kegiatan_id' => $jenisB->id_jenis_kegiatan,
            ],

            // Jenis C (Tahun Berikutnya)
            [
                'kode_kegiatan' => 'KEG006',
                'nama_kegiatan' => 'Penyusunan Rencana Kerja Pemerintah Desa (RKPDes)',
                'bulan' => 9,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 25,
                'batas_akhir_upload' => 30,
                'dasar_hukum' => 'Permendesa No. 21 Tahun 2020 tentang Prioritas Penggunaan Dana Desa',
                'jenis_kegiatan_id' => $jenisC->id_jenis_kegiatan,
            ],
            [
                'kode_kegiatan' => 'KEG007',
                'nama_kegiatan' => 'Penyusunan RAPBDes (Rencana Anggaran Pendapatan dan Belanja Desa)',
                'bulan' => 11,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 25,
                'batas_akhir_upload' => 30,
                'dasar_hukum' => 'Peraturan Bupati tentang Pedoman Penyusunan APBDes',
                'jenis_kegiatan_id' => $jenisC->id_jenis_kegiatan,
            ],
        ];

        foreach ($data as $item) {
            // Validasi agar batas upload selalu > tanggal selesai
            if ($item['batas_akhir_upload'] <= $item['tanggal_selesai']) {
                $this->command->warn("⚠️ Batas upload untuk {$item['kode_kegiatan']} diperbaiki otomatis.");
                $item['batas_akhir_upload'] = $item['tanggal_selesai'] + 1;
            }

            $item['tahun_anggaran_id'] = $tahunAktif->id_tahun_anggaran;
            $item['status'] = 'active';

            Kegiatan::updateOrCreate(
                [
                    'tahun_anggaran_id' => $item['tahun_anggaran_id'],
                    'kode_kegiatan' => $item['kode_kegiatan'],
                ],
                $item
            );
        }

        $this->command->info('✅ Seeder Kegiatan berhasil dijalankan dengan validasi batas upload.');
    }
}
