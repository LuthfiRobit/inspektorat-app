<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['kode_kecamatan' => 'KEC001', 'nama_kecamatan' => 'Kecamatan Paiton', 'status' => 'active'],
            ['kode_kecamatan' => 'KEC002', 'nama_kecamatan' => 'Kecamatan Pakuniran', 'status' => 'active'],
        ];

        foreach ($data as $item) {
            Kecamatan::firstOrCreate(
                ['kode_kecamatan' => $item['kode_kecamatan']],
                $item
            );
        }
    }
}
