<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil kecamatan berdasarkan nama
        $paiton = Kecamatan::where('nama_kecamatan', 'Kecamatan Paiton')->first();
        $pakuniran = Kecamatan::where('nama_kecamatan', 'Kecamatan Pakuniran')->first();

        if (!$paiton || !$pakuniran) {
            $this->command->error('Kecamatan tidak ditemukan. Pastikan seeder Kecamatan dijalankan terlebih dahulu.');
            return;
        }

        $data = [
            [
                'kecamatan_id' => $paiton->id_kecamatan,
                'kode_desa' => 'DES001',
                'nama_desa' => 'Desa Alas Tengah',
                'status' => 'active',
            ],
            [
                'kecamatan_id' => $paiton->id_kecamatan,
                'kode_desa' => 'DES002',
                'nama_desa' => 'Desa Sukodadi',
                'status' => 'active',
            ],
            [
                'kecamatan_id' => $pakuniran->id_kecamatan,
                'kode_desa' => 'DES003',
                'nama_desa' => 'Desa Bucor Kulon',
                'status' => 'active',
            ],
            [
                'kecamatan_id' => $pakuniran->id_kecamatan,
                'kode_desa' => 'DES004',
                'nama_desa' => 'Desa Bucor Wetan',
                'status' => 'active',
            ],
        ];

        foreach ($data as $item) {
            Desa::firstOrCreate(
                ['kode_desa' => $item['kode_desa']],
                $item
            );
        }

        $this->command->info('Seeder Desa berhasil dijalankan dengan data tetap.');
    }
}
