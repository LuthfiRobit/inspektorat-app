<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoryDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dokumenList = DB::table('dokumen_persyaratan')->get();

        if ($dokumenList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada data dokumen_persyaratan ditemukan.');
            return;
        }

        foreach ($dokumenList as $dokumen) {
            // Ambil user yang membuat dokumen
            $changedBy = $dokumen->created_by;

            // Masukkan 1 entri history awal sebagai simulasi upload awal
            DB::table('history_dokumen')->insert([
                'dokumen_id'            => $dokumen->id_dokumen,
                'action'                => 'upload',
                'nama_file_sebelum'     => null,
                'path_file_sebelum'     => null,
                'catatan_perubahan'     => 'Dokumen awal diunggah.',
                'changed_by'            => $changedBy,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            // Optional: Bisa tambahkan 1 riwayat tambahan simulasi revisi atau approve
            if (rand(0, 1)) {
                DB::table('history_dokumen')->insert([
                    'dokumen_id'            => $dokumen->id_dokumen,
                    'action'                => 'approve',
                    'nama_file_sebelum'     => $dokumen->nama_file,
                    'path_file_sebelum'     => $dokumen->path_file,
                    'catatan_perubahan'     => 'Dokumen telah disetujui oleh verifikator.',
                    'changed_by'            => $changedBy,
                    'created_at'            => now()->addMinutes(5),
                    'updated_at'            => now()->addMinutes(5),
                ]);
            }
        }

        $this->command->info('✅ Seeder history_dokumen selesai.');
    }
}
