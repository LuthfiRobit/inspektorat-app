<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DokumenPersyaratanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jawabanList = DB::table('jawaban_pertanyaan')->get();

        if ($jawabanList->isEmpty()) {
            $this->command->warn('⛔ Tidak ada data jawaban_pertanyaan ditemukan.');
            return;
        }

        foreach ($jawabanList as $jawaban) {
            // Ambil persyaratan yang sesuai dengan pertanyaan_id dari jawaban
            $persyaratanList = DB::table('persyaratan')
                ->where('pertanyaan_kegiatan_id', $jawaban->pertanyaan_id)
                ->get();

            foreach ($persyaratanList as $persyaratan) {
                $createdBy = DB::table('laporan_kegiatan')
                    ->where('id_laporan', $jawaban->laporan_id)
                    ->value('created_by');

                DB::table('dokumen_persyaratan')->insert([
                    'jawaban_id'      => $jawaban->id_jawaban,
                    'persyaratan_id'  => $persyaratan->id_persyaratan,
                    'nama_file'       => 'Dokumen_' . Str::random(5) . '.pdf',
                    'path_file'       => 'uploads/dokumen/' . Str::uuid() . '.pdf',
                    'status'          => 'submitted',
                    'catatan_revisi'  => null,
                    'version'         => 1,
                    'is_current'      => true,
                    'created_by'      => $createdBy,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        $this->command->info('✅ Seeder dokumen_persyaratan selesai.');
    }
}
