<?php

namespace App\Repositories;

use App\Models\Desa;
use App\Models\LaporanKegiatan;
use App\Models\JawabanPertanyaan;
use App\Models\DokumenPersyaratan;
use App\Models\Kegiatan;
use App\Models\PetugasWilayahBinaan;
use Carbon\Carbon;
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

    public function getDetail($desaId, $kegiatanId, $laporanId = null, $bulan = null, $tahun = null)
    {
        // ============================
        // 1. Ambil desa
        // ============================
        $desa = Desa::getRelationship($desaId);
        if (!$desa) {
            throw new \Exception("Desa tidak ditemukan");
        }

        // ============================
        // 2. Ambil kegiatan
        // ============================
        $kegiatan = Kegiatan::getRelationship($kegiatanId);
        if (!$kegiatan) {
            throw new \Exception("Kegiatan tidak ditemukan");
        }

        // ============================
        // 3. Ambil laporan (jika ada)
        // ============================
        $laporanQuery = LaporanKegiatan::where('desa_id', $desaId)
            ->where('kegiatan_id', $kegiatanId);

        if ($laporanId) {
            $laporan = LaporanKegiatan::find($laporanId); // Strict by ID
        } elseif ($bulan && $tahun) {
            $laporan = $laporanQuery->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
        } else {
            // Fallback minimal (should ideally not happen in new logic)
            $laporan = $laporanQuery->first();
        }

        // Tentukan tahun & bulan (Prioritas: Laporan -> Input Params -> Master Kegiatan)
        $tahun = (int) ($laporan->tahun ?? ($tahun ?? $kegiatan['tahun']));
        $bulan = (int) ($laporan->bulan ?? ($bulan ?? $kegiatan['bulan']));

        // ============================
        // 4. Hitung tanggal target
        // ============================
        // Note: $kegiatan is array from getRelationship
        $tanggalTarget = $this->calculateTanggalTarget($kegiatan, $tahun, $bulan);

        // ============================
        // 5. Hitung timeline status
        // ============================
        $timeline = $this->calculateTimelineStatus($laporan, $tanggalTarget);

        // Map Nama Bulan
        $bulanNama = match ((int) $bulan) {
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            default => '-'
        };

        // ============================
        // 6. Return structured data
        // ============================
        return [
            'desa' => [
                'id_desa' => $desa->id_desa,
                'nama_desa' => $desa->nama_desa,
                'kode_desa' => $desa->kode_desa,
                'nama_kecamatan' => $desa->nama_kecamatan
            ],

            'kegiatan' => [
                'id_kegiatan' => $kegiatan['id_kegiatan'],
                'nama_kegiatan' => $kegiatan['nama_kegiatan'],
                'kode_kegiatan' => $kegiatan['kode_kegiatan'],
                'tahun_anggaran' => $kegiatan['tahun'],
                'jenis_kegiatan' => $kegiatan['nama_jenis'],
                'bulan_master' => $kegiatan['nama_bulan'], // Original master month
                'tanggal_mulai' => $kegiatan['tanggal_mulai'],
                'tanggal_selesai' => $kegiatan['tanggal_selesai'],
                'batas_akhir_upload' => $kegiatan['batas_akhir_upload'],
                'dasar_hukum' => $kegiatan['dasar_hukum'],
                'frekuensi_pelaporan' => $kegiatan['frekuensi_pelaporan'] ?? null,
                'bulan' => $kegiatan['bulan'], // Insidentil usage
                'bulan_mulai' => $kegiatan['bulan_mulai'], // Rutin usage
                'bulan_selesai' => $kegiatan['bulan_selesai'], // Rutin usage
            ],

            'context' => [
                'bulan' => $bulan,
                'bulan_nama' => $bulanNama,
                'tahun' => $tahun,
            ],

            'laporan' => $laporan ? [
                'id_laporan' => $laporan->id_laporan,
                'status' => $laporan->status,
                'tanggal_submit' => $laporan->tanggal_submit,
                'tanggal_approve' => $laporan->tanggal_approve,
                'catatan_approval' => $laporan->catatan_approval
            ] : null,

            'tanggal_target' => optional($tanggalTarget)->toDateString(),
            'timeline_status' => $timeline['status'],
            'days_until_deadline' => $timeline['days'],
        ];
    }

    /**
     * Hitung tanggal target dari kegiatan
     */
    private function calculateTanggalTarget($kegiatan, $tahun, $bulan)
    {
        // Perbaikan: Akses sebagai array
        if (!$kegiatan['batas_akhir_upload']) {
            return null;
        }

        $baseDate = Carbon::create((int) $tahun, (int) $bulan, 1);

        // Perbaikan: Akses sebagai array
        $tanggalSelesai = (int) ($kegiatan['tanggal_selesai'] ?: $baseDate->daysInMonth);
        $tanggalSelesai = min($tanggalSelesai, $baseDate->daysInMonth);

        // Perbaikan: Akses sebagai array
        return Carbon::create((int) $tahun, (int) $bulan, $tanggalSelesai)
            ->addDays((int) $kegiatan['batas_akhir_upload'])
            ->startOfDay();
    }

    /**
     * Hitung status timeline
     */
    private function calculateTimelineStatus($laporan, $tanggalTarget)
    {
        if (!$tanggalTarget) {
            return ['status' => 'Tidak Ada Target', 'days' => null];
        }

        $now = Carbon::now();
        $days = $now->diffInDays($tanggalTarget, false);

        if ($laporan && $laporan->status === 'approved') {
            return ['status' => 'Selesai', 'days' => $days];
        }

        if ($days > 5)
            return ['status' => 'Menunggu', 'days' => $days];
        if ($days >= 0)
            return ['status' => 'Tenggang', 'days' => $days];
        return ['status' => 'Terlambat', 'days' => $days];
    }

    /**
     * Get consolidated logic for Tarik Data without N+1 queries.
     * Generates all desa x kegiatan combinations according to filters.
     */
    public function getTarikDataList($user, $filters)
    {
        // 1. Get Desa based on User Role
        $desaQuery = DB::table('desa as d')
            ->select('d.id_desa', 'd.nama_desa', 'd.kode_desa', 'd.kecamatan_id', 'kec.nama_kecamatan')
            ->leftJoin('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->where('d.status', 'active');

        $petugas = $user->petugas;
        if ($petugas) {
            if ($petugas->desa_id) {
                // Desa - show only their desa
                $desaQuery->where('d.id_desa', $petugas->desa_id);
            } elseif ($petugas->kecamatan_id) {
                // Kecamatan - show all desa in their kecamatan
                $desaBinaanIds = PetugasWilayahBinaan::where('petugas_id', $petugas->id_petugas)
                    ->whereNotNull('desa_id')
                    ->pluck('desa_id');

                if ($desaBinaanIds->isNotEmpty()) {
                    $desaQuery->whereIn('d.id_desa', $desaBinaanIds);
                } else {
                    $desaQuery->where('d.kecamatan_id', $petugas->kecamatan_id);
                }
            } else {
                // Inspektorat - check wilayah binaan
                $kecamatanBinaanIds = PetugasWilayahBinaan::where('petugas_id', $petugas->id_petugas)
                    ->whereNotNull('kecamatan_id')
                    ->pluck('kecamatan_id');

                if ($kecamatanBinaanIds->isNotEmpty()) {
                    $desaQuery->whereIn('d.kecamatan_id', $kecamatanBinaanIds);
                }
            }
        }

        $desaList = $desaQuery->get();

        // 2. Get Kegiatan based on filter_tahun
        $kegiatanQuery = DB::table('kegiatan as k')
            ->select(
                'k.id_kegiatan',
                'k.nama_kegiatan',
                'k.kode_kegiatan',
                'k.tanggal_mulai',
                'k.tanggal_selesai',
                'k.bulan',
                // New Columns for Recurring Logic
                'k.frekuensi_pelaporan',
                'k.bulan_mulai',
                'k.bulan_selesai',
                'k.batas_akhir_upload',
                'jk.nama_jenis as jenis_kegiatan',
                'ta.tahun as tahun_anggaran'
            )
            ->leftJoin('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->leftJoin('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->where('k.status', 'active')
            ->where('jk.status', 'active');

        if (!empty($filters['filter_tahun'])) {
            $kegiatanQuery->where('ta.id_tahun_anggaran', $filters['filter_tahun']);
        }

        $kegiatanList = $kegiatanQuery->get();

        // Normalize Types
        $kegiatanList = $kegiatanList->map(function ($kegiatan) {
            $kegiatan->id_kegiatan = (int) $kegiatan->id_kegiatan;
            $kegiatan->tahun_anggaran = (int) $kegiatan->tahun_anggaran;
            $kegiatan->bulan = (int) $kegiatan->bulan;
            $kegiatan->batas_akhir_upload = (int) $kegiatan->batas_akhir_upload;
            $kegiatan->frekuensi_pelaporan = $kegiatan->frekuensi_pelaporan ? (int) $kegiatan->frekuensi_pelaporan : null;
            $kegiatan->bulan_mulai = $kegiatan->bulan_mulai ? (int) $kegiatan->bulan_mulai : null;
            $kegiatan->bulan_selesai = $kegiatan->bulan_selesai ? (int) $kegiatan->bulan_selesai : null;
            $kegiatan->tanggal_selesai = $kegiatan->tanggal_selesai ? (int) $kegiatan->tanggal_selesai : null;
            $kegiatan->tanggal_mulai = $kegiatan->tanggal_mulai ? (int) $kegiatan->tanggal_mulai : null;
            return $kegiatan;
        });

        // Resolve necessary month range
        $bulanAwal = (int) ($filters['filter_bulan_awal'] ?? 1);
        $bulanAkhir = !empty($filters['filter_bulan_akhir']) ? (int) $filters['filter_bulan_akhir'] : $bulanAwal;
        $rangeBulanFilter = range($bulanAwal, $bulanAkhir);

        // Batch Query all matching Laporan in db to prevent N+1 queries.
        $laporanDbQuery = DB::table('laporan_kegiatan as lk')
            ->whereIn('lk.bulan', $rangeBulanFilter)
            ->whereIn('lk.desa_id', $desaList->pluck('id_desa')->toArray())
            ->whereIn('lk.kegiatan_id', $kegiatanList->pluck('id_kegiatan')->toArray());

        $laporanDbResult = $laporanDbQuery->get()->groupBy(function ($item) {
            return $item->desa_id . '_' . $item->kegiatan_id . '_' . $item->bulan;
        });

        $result = collect();
        $targetStatus = $filters['filter_status'] ?? null;

        foreach ($desaList as $desa) {
            foreach ($kegiatanList as $kegiatan) {

                // --- Determine Target Months based on Frequency ---
                $targetMonths = [];
                if ($kegiatan->frekuensi_pelaporan) {
                    $startMonth = $kegiatan->bulan_mulai ?? 1;
                    $endMonth = $kegiatan->bulan_selesai ?? 12;
                    $step = $kegiatan->frekuensi_pelaporan;
                    for ($m = $startMonth; $m <= $endMonth; $m += $step) {
                        $targetMonths[] = $m;
                    }
                } else {
                    $targetMonths[] = $kegiatan->bulan; // Insidentil
                }

                foreach ($targetMonths as $targetBulan) {
                    // Check against filter
                    if (!in_array($targetBulan, $rangeBulanFilter)) {
                        continue;
                    }

                    $key = $desa->id_desa . '_' . $kegiatan->id_kegiatan . '_' . $targetBulan;
                    $existingLaporanArr = $laporanDbResult->get($key);
                    $existingLaporan = $existingLaporanArr ? $existingLaporanArr->first() : null;

                    $status = $existingLaporan->status ?? 'belum_dilaporkan';

                    // Apply status filter
                    if ($targetStatus && $status !== $targetStatus) {
                        continue;
                    }

                    $record = [
                        'id_laporan' => $existingLaporan->id_laporan ?? null,
                        'tahun' => $kegiatan->tahun_anggaran,
                        'bulan' => $targetBulan,
                        'nama_kecamatan' => $desa->nama_kecamatan,
                        'nama_desa' => $desa->nama_desa,
                        'jenis_kegiatan' => $kegiatan->jenis_kegiatan,
                        'nama_kegiatan' => $kegiatan->nama_kegiatan,
                        'status' => $status,
                        'status_display' => LaporanKegiatan::getStatusDisplayForUser($status),
                        'status_class' => LaporanKegiatan::getStatusClass($status),
                    ];

                    $result->push($record);
                }
            }
        }

        return $result;
    }
}
