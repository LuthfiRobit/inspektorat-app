<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kegiatan;
use App\Models\Petugas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $desaList = Desa::where('status', 'active')->get();
        $kegiatanList = DB::table('kegiatan')->get();

        if ($desaList->isEmpty() || $kegiatanList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada data desa atau kegiatan yang tersedia.');
            return;
        }

        foreach ($desaList as $desa) {
            foreach ($kegiatanList as $kegiatan) {
                // Ambil petugas kecamatan berdasarkan kecamatan_id dari desa
                $petugasKecamatan = Petugas::where('kecamatan_id', $desa->kecamatan_id)
                    ->whereNull('desa_id')
                    ->whereNotNull('user_id')
                    ->where('status', 'active')
                    ->first();

                if (!$petugasKecamatan) {
                    $this->command->warn("⚠️ Tidak ditemukan petugas kecamatan aktif untuk desa {$desa->nama_desa}. Lewati.");
                    continue;
                }

                // Ambil user inspektorat sebagai approver (optional)
                $petugasInspektorat = Petugas::whereNull('kecamatan_id')
                    ->whereNull('desa_id')
                    ->whereNotNull('user_id')
                    ->where('status', 'active')
                    ->inRandomOrder()
                    ->first();

                DB::table('laporan_kegiatan')->insert([
                    'desa_id' => $desa->id_desa,
                    'kegiatan_id' => $kegiatan->id_kegiatan,
                    'tahun' => Carbon::now()->year,
                    'bulan' => rand(1, 12),
                    'status' => 'draft',
                    'tanggal_target' => Carbon::create(Carbon::now()->year, rand(1, 12), rand(1, 28))->toDateString(),
                    'tanggal_submit' => null,
                    'tanggal_approve' => null,
                    'approved_by' => $petugasInspektorat?->user_id,
                    'catatan_approval' => null,
                    'created_by' => $petugasKecamatan->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Seeder laporan_kegiatan selesai dibuat.');
    }
}
