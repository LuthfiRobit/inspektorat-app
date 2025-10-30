<?php

namespace App\Repositories;

use App\Models\LaporanKegiatan;
use App\Models\JawabanPertanyaan;
use App\Models\DokumenPersyaratan;
use Illuminate\Support\Facades\DB;

class LaporanKegiatanRepository
{
    /**
     * Get laporan data with relationships
     */
    public function getWithRelationships($id)
    {
        return LaporanKegiatan::getWithRelationships($id);
    }

    /**
     * Get laporan list for user with filters
     */
    public function getListForUser($user, $filters)
    {
        return LaporanKegiatan::getListForUser($user, $filters);
    }

    /**
     * Get jawaban with dokumen for laporan
     */
    public function getJawabanWithDokumen($laporanId)
    {
        return [
            'jawaban' => JawabanPertanyaan::where('laporan_id', $laporanId)->get(),
            'dokumen' => DokumenPersyaratan::whereIn(
                'jawaban_id',
                JawabanPertanyaan::where('laporan_id', $laporanId)->pluck('id_jawaban')
            )->where('is_current', true)->get()
        ];
    }
}
