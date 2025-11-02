<?php
// app/Repositories/ScoringDesaRepository.php

namespace App\Repositories;

use App\Models\ScoringDesa;

class ScoringDesaRepository
{
    public function updateOrCreate(array $conditions, array $data)
    {
        return ScoringDesa::updateOrCreate($conditions, $data);
    }

    public function findByLaporan($laporanId)
    {
        return ScoringDesa::where('laporan_id', $laporanId)->first();
    }

    public function getRanking($tahun, $bulan)
    {
        return ScoringDesa::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->whereNotNull('peringkat')
            ->orderBy('peringkat', 'ASC')
            ->get();
    }
}
