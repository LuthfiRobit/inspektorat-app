<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KeterlambatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua laporan
        $laporanList = DB::table('laporan_kegiatan')->get();

        if ($laporanList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada data laporan_kegiatan ditemukan.');
            return;
        }

        foreach ($laporanList as $laporan) {
            // Simulasikan tanggal_upload lebih lambat dari tanggal_target 30% kasus
            $tanggalTarget = Carbon::parse($laporan->tanggal_target);
            $tanggalUpload = $laporan->tanggal_submit
                ? Carbon::parse($laporan->tanggal_submit)
                : $tanggalTarget->copy()->addDays(rand(1, 10));

            if ($tanggalUpload->lessThanOrEqualTo($tanggalTarget)) {
                continue; // Tidak terlambat, tidak disimpan
            }

            $hariKeterlambatan = $tanggalUpload->diffInDays($tanggalTarget);

            DB::table('keterlambatan')->insert([
                'laporan_id'         => $laporan->id_laporan,
                'desa_id'            => $laporan->desa_id,
                'tanggal_target'     => $tanggalTarget->toDateString(),
                'tanggal_upload'     => $tanggalUpload->toDateString(),
                'hari_keterlambatan' => $hariKeterlambatan,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        $this->command->info('✅ Seeder keterlambatan selesai.');
    }
}
