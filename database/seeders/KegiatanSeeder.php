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
                'bulan' => 1, // Insidentil: Bulan Pelaporan
                'frekuensi_pelaporan' => null,
                'bulan_mulai' => null,
                'bulan_selesai' => null,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 15,
                'batas_akhir_upload' => 5, // 5 hari setelah tanggal selesai
                'dasar_hukum' => 'Peraturan Bupati No. 12 Tahun 2020 tentang Pedoman Penyusunan APBDes',
                'jenis_kegiatan_id' => $jenisA->id_jenis_kegiatan,
            ],
            // Contoh RUTIN Bulanan
            [
                'kode_kegiatan' => 'KEG002',
                'nama_kegiatan' => 'Laporan Pertanggungjawaban Realisasi Pelaksanaan APBDes (LPRP-APBDes) - Bulanan',
                'bulan' => 1, // Start Month
                'frekuensi_pelaporan' => 1, // Every 1 month
                'bulan_mulai' => 1,
                'bulan_selesai' => 12, // Valid Jan - Dec
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 10,
                'batas_akhir_upload' => 5,
                'dasar_hukum' => 'Permendagri No. 20 Tahun 2018 tentang Pengelolaan Keuangan Desa',
                'jenis_kegiatan_id' => $jenisA->id_jenis_kegiatan,
            ],
            // Contoh RUTIN Triwulanan
            [
                'kode_kegiatan' => 'KEG003',
                'nama_kegiatan' => 'Laporan Keterangan Pertanggungjawaban (LKPRP-APBDes) - Triwulanan',
                'bulan' => 3, // Start Month
                'frekuensi_pelaporan' => 3, // Every 3 months
                'bulan_mulai' => 3,
                'bulan_selesai' => 12,
                'tanggal_mulai' => 1,
                'tanggal_selesai' => 15,
                'batas_akhir_upload' => 7,
                'dasar_hukum' => 'UU No. 6 Tahun 2014 tentang Desa',
                'jenis_kegiatan_id' => $jenisA->id_jenis_kegiatan,
            ],

            // // Jenis B (Tahun Berjalan) - Insidentil
            // [
            //     'kode_kegiatan' => 'KEG004',
            //     'nama_kegiatan' => 'Proses Pengajuan sampai Penerimaan Siltap',
            //     'bulan' => 4,
            //     'frekuensi_pelaporan' => null,
            //     'bulan_mulai' => null,
            //     'bulan_selesai' => null,
            //     'tanggal_mulai' => 1,
            //     'tanggal_selesai' => 15,
            //     'batas_akhir_upload' => 5,
            //     'dasar_hukum' => 'Peraturan Bupati tentang Penggajian Pegawai Desa',
            //     'jenis_kegiatan_id' => $jenisB->id_jenis_kegiatan,
            // ],
            // // Contoh RUTIN Semesteran
            // [
            //     'kode_kegiatan' => 'KEG005',
            //     'nama_kegiatan' => 'Monitoring Pelaksanaan Pembangunan Desa - Semesteran',
            //     'bulan' => 6, // Start Month
            //     'frekuensi_pelaporan' => 6,
            //     'bulan_mulai' => 6,
            //     'bulan_selesai' => 12,
            //     'tanggal_mulai' => 1,
            //     'tanggal_selesai' => 25,
            //     'batas_akhir_upload' => 10,
            //     'dasar_hukum' => 'Peraturan Desa tentang Pelaksanaan Pembangunan',
            //     'jenis_kegiatan_id' => $jenisB->id_jenis_kegiatan,
            // ],

            // // Jenis C (Tahun Berikutnya)
            // [
            //     'kode_kegiatan' => 'KEG006',
            //     'nama_kegiatan' => 'Penyusunan Rencana Kerja Pemerintah Desa (RKPDes)',
            //     'bulan' => 9,
            //     'frekuensi_pelaporan' => null,
            //     'bulan_mulai' => null,
            //     'bulan_selesai' => null,
            //     'tanggal_mulai' => 1,
            //     'tanggal_selesai' => 25,
            //     'batas_akhir_upload' => 5,
            //     'dasar_hukum' => 'Permendesa No. 21 Tahun 2020 tentang Prioritas Penggunaan Dana Desa',
            //     'jenis_kegiatan_id' => $jenisC->id_jenis_kegiatan,
            // ],
            // [
            //     'kode_kegiatan' => 'KEG007',
            //     'nama_kegiatan' => 'Penyusunan RAPBDes (Rencana Anggaran Pendapatan dan Belanja Desa)',
            //     'bulan' => 11,
            //     'frekuensi_pelaporan' => null,
            //     'bulan_mulai' => null,
            //     'bulan_selesai' => null,
            //     'tanggal_mulai' => 1,
            //     'tanggal_selesai' => 25,
            //     'batas_akhir_upload' => 5,
            //     'dasar_hukum' => 'Peraturan Bupati tentang Pedoman Penyusunan APBDes',
            //     'jenis_kegiatan_id' => $jenisC->id_jenis_kegiatan,
            // ],
        ];

        foreach ($data as $item) {
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
