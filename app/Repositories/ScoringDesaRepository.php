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

   /**
     * GET DATA SCORING DESA DENGAN FILTER
     * 
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getDesaScoring(array $filters = [])
    {
        return ScoringDesa::getScoringDesa($filters);
    }

    /**
     * GET DATA SCORING KECAMATAN DENGAN FILTER
     * 
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getKecamatanScoring(array $filters = [])
    {
        return ScoringDesa::getScoringKecamatan($filters);
    }
}
