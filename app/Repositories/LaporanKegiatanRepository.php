<?php

namespace App\Repositories;

use App\Models\Desa;
use App\Models\LaporanKegiatan;
use App\Models\JawabanPertanyaan;
use App\Models\DokumenPersyaratan;
use App\Models\Kegiatan;
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
        $tahun = $laporan->tahun ?? ($tahun ?? $kegiatan['tahun']);
        $bulan = $laporan->bulan ?? ($bulan ?? $kegiatan['bulan']);

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

        $baseDate = Carbon::create($tahun, $bulan, 1);

        // Perbaikan: Akses sebagai array
        $tanggalSelesai = $kegiatan['tanggal_selesai'] ?: $baseDate->daysInMonth;
        $tanggalSelesai = min($tanggalSelesai, $baseDate->daysInMonth);

        // Perbaikan: Akses sebagai array
        return Carbon::create($tahun, $bulan, $tanggalSelesai)
            ->addDays($kegiatan['batas_akhir_upload'])
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
}
