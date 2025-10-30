<?php

namespace Database\Seeders;

use App\Models\TahunAnggaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TahunAnggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');

        $data = [
            [
                'tahun' => $currentYear - 1,
                'keterangan' => 'Tahun anggaran ' . ($currentYear - 1),
                'status' => 'inactive'
            ],
            [
                'tahun' => $currentYear,
                'keterangan' => 'Tahun anggaran ' . $currentYear,
                'status' => 'active'
            ],
            [
                'tahun' => $currentYear + 1,
                'keterangan' => 'Tahun anggaran ' . ($currentYear + 1),
                'status' => 'inactive'
            ],
        ];

        foreach ($data as $item) {
            TahunAnggaran::firstOrCreate(
                ['tahun' => $item['tahun']],
                $item
            );
        }

        // Ensure only one active year
        $activeYears = TahunAnggaran::where('status', 'active')->get();
        if ($activeYears->count() > 1) {
            // Deactivate all except the current year
            TahunAnggaran::where('tahun', '!=', $currentYear)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }
    }
}
