<?php
// app/Repositories/KeterlambatanRepository.php

namespace App\Repositories;

use App\Models\Keterlambatan;

class KeterlambatanRepository
{
    public function create(array $data)
    {
        return Keterlambatan::create($data);
    }

    public function findByLaporan($laporanId)
    {
        return Keterlambatan::where('laporan_id', $laporanId)->first();
    }

    public function deleteByLaporan($laporanId)
    {
        return Keterlambatan::where('laporan_id', $laporanId)->delete();
    }
}
