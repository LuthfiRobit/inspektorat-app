<?php

namespace Database\Seeders;

use App\Models\Petugas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoryLaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laporanList = DB::table('laporan_kegiatan')->get();

        if ($laporanList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada laporan_kegiatan ditemukan. Seeder history dibatalkan.');
            return;
        }

        foreach ($laporanList as $laporan) {
            $histories = [];

            // Simulasikan perubahan status (draft -> submitted -> approved/rejected)
            $timeline = [
                ['from' => 'draft', 'to' => 'submitted'],
                ['from' => 'submitted', 'to' => rand(0, 1) ? 'approved' : 'rejected'],
            ];

            foreach ($timeline as $step) {
                $statusTo = $step['to'];

                // Tentukan siapa yang mengubah
                $changedBy = null;
                if ($statusTo === 'submitted') {
                    $changedBy = $laporan->created_by; // petugas kecamatan
                } else {
                    // Ambil user inspektorat secara acak
                    $petugasInspektorat = Petugas::whereNull('kecamatan_id')
                        ->whereNull('desa_id')
                        ->whereNotNull('user_id')
                        ->where('status', 'active')
                        ->inRandomOrder()
                        ->first();

                    $changedBy = $petugasInspektorat?->user_id;
                }

                $histories[] = [
                    'laporan_id' => $laporan->id_laporan,
                    'status_sebelum' => $step['from'],
                    'status_sesudah' => $step['to'],
                    'catatan_perubahan' => 'Perubahan status dari ' . $step['from'] . ' ke ' . $step['to'],
                    'changed_by' => $changedBy,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(0, 10)),
                ];
            }

            // Insert semua history untuk laporan ini
            DB::table('history_laporan')->insert($histories);
        }

        $this->command->info('✅ History laporan kegiatan berhasil disimulasikan.');
    }
}
