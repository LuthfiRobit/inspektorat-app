<?php

namespace App\Services;

use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Kegiatan;
use App\Models\LaporanKegiatan;
use App\Models\TahunAnggaran;
use App\Models\JenisKegiatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get dashboard summary for all roles in optimized queries
     */
    public function getSummary(array $filters = [])
    {
        $user = Auth::user();
        $petugas = $user->petugas;
        $role = $this->determineUserRole($user);

        // Apply role-based scope restrictions
        $filters = $this->applyRoleScope($filters, $user, $role);

        // Cache key based on filters and user
        $cacheKey = 'dashboard_summary_' . $user->id . '_' . md5(json_encode($filters));

        // Cache for 5 minutes
        return Cache::remember($cacheKey, 300, function () use ($user, $filters, $role, $petugas) {
            return $this->buildSummaryData($user, $filters, $role, $petugas);
        });
    }

    /**
     * Determine user role
     */
    private function determineUserRole($user): string
    {
        if (!$user->petugas) {
            return 'admin';
        }

        $petugas = $user->petugas;

        if ($petugas->desa_id) {
            return 'desa';
        }

        if ($petugas->kecamatan_id) {
            return 'kecamatan';
        }

        return 'admin';
    }

    /**
     * Apply role scope to filters
     */
    private function applyRoleScope(array $filters, $user, string $role): array
    {
        $petugas = $user->petugas;

        switch ($role) {
            case 'desa':
                $filters['desa_id'] = $petugas->desa_id;
                break;

            case 'kecamatan':
                $filters['kecamatan_id'] = $petugas->kecamatan_id;
                break;
        }

        return $filters;
    }

    /**
     * Build summary data
     */
    private function buildSummaryData($user, array $filters, string $role, $petugas): array
    {
        $summary = [];

        // 1. Get all counts in parallel queries
        $summary = array_merge(
            $this->getTerritoryCounts($filters, $role, $petugas),
            $this->getLaporanCounts($filters, $role, $petugas),
            $this->getKegiatanCounts($filters, $role, $petugas)
        );

        return [
            'summary' => $summary,
            'user_role' => $role
        ];
    }

    /**
     * Get territory counts (kecamatan, desa)
     */
    private function getTerritoryCounts(array $filters, string $role, $petugas): array
    {
        $counts = [];

        // Kecamatan count (only for admin)
        if ($role === 'admin') {
            $query = Kecamatan::where('status', 'active');

            if (!empty($filters['kecamatan_id'])) {
                $query->where('id_kecamatan', $filters['kecamatan_id']);
            }

            $counts['total_kecamatan'] = $query->count();
        }

        // Desa count (for admin and kecamatan)
        if (in_array($role, ['admin', 'kecamatan'])) {
            $query = Desa::where('status', 'active');

            if ($role === 'kecamatan') {
                $query->where('kecamatan_id', $petugas->kecamatan_id);
            } elseif (!empty($filters['kecamatan_id'])) {
                $query->where('kecamatan_id', $filters['kecamatan_id']);
            }

            if (!empty($filters['desa_id'])) {
                $query->where('id_desa', $filters['desa_id']);
            }

            $counts['total_desa'] = $query->count();
        }

        return $counts;
    }

    /**
     * Get laporan counts with status breakdown
     */
    private function getLaporanCounts(array $filters, string $role, $petugas): array
    {
        // Build base query for laporan
        $query = DB::table('laporan_kegiatan as lk')
            ->selectRaw("
                COUNT(*) as total_laporan,
                SUM(CASE WHEN lk.status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN lk.status = 'submitted' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN lk.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN lk.status = 'revision' THEN 1 ELSE 0 END) as revision,
                SUM(CASE WHEN lk.status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ");

        // Join for filters
        $query->leftJoin('desa as d', 'lk.desa_id', '=', 'd.id_desa')
            ->leftJoin('kegiatan as k', 'lk.kegiatan_id', '=', 'k.id_kegiatan');

        // Apply role scope
        $this->applyLaporanScope($query, $role, $petugas, $filters);

        // Apply filters
        $this->applyLaporanFilters($query, $filters);

        $result = $query->first();

        return [
            'total_laporan' => (int) ($result->total_laporan ?? 0),
            'total_laporan_draft' => (int) ($result->draft ?? 0),
            'total_laporan_pending' => (int) ($result->submitted ?? 0),
            'total_laporan_approved' => (int) ($result->approved ?? 0),
            'total_laporan_revisi' => (int) ($result->revision ?? 0),
            'total_laporan_rejected' => (int) ($result->rejected ?? 0),

            // Aliases for different views
            'laporan_disetujui' => (int) ($result->approved ?? 0),
            'laporan_revisi' => (int) ($result->revision ?? 0),
            'laporan_pending' => (int) ($result->submitted ?? 0),
        ];
    }

    /**
     * Apply laporan scope based on role
     */
    private function applyLaporanScope($query, string $role, $petugas, array &$filters)
    {
        switch ($role) {
            case 'desa':
                $query->where('d.id_desa', $petugas->desa_id);
                $filters['desa_id'] = $petugas->desa_id; // Force this filter
                break;

            case 'kecamatan':
                $query->where('d.kecamatan_id', $petugas->kecamatan_id);
                $filters['kecamatan_id'] = $petugas->kecamatan_id; // Force this filter
                break;
        }
    }

    /**
     * Apply laporan filters
     */
    private function applyLaporanFilters($query, array $filters)
    {
        // Tahun filter
        if (!empty($filters['tahun'])) {
            $query->where('lk.tahun', $filters['tahun']);
        }

        // Bulan filter
        if (!empty($filters['bulan'])) {
            $query->where('lk.bulan', $filters['bulan']);
        }

        // Jenis kegiatan filter
        if (!empty($filters['jenis_kegiatan_id'])) {
            $query->where('k.jenis_kegiatan_id', $filters['jenis_kegiatan_id']);
        }

        // Kecamatan filter (only for admin)
        if (!empty($filters['kecamatan_id']) && !isset($filters['kecamatan_id_from_scope'])) {
            $query->where('d.kecamatan_id', $filters['kecamatan_id']);
        }

        // Desa filter
        if (!empty($filters['desa_id']) && !isset($filters['desa_id_from_scope'])) {
            $query->where('d.id_desa', $filters['desa_id']);
        }
    }

    /**
     * Get kegiatan counts
     */
    private function getKegiatanCounts(array $filters, string $role, $petugas): array
    {
        $counts = [];

        // Kegiatan count (for admin and desa)
        if (in_array($role, ['admin', 'desa'])) {
            $query = Kegiatan::where('status', 'active');

            // Filter by tahun anggaran
            if (!empty($filters['tahun'])) {
                $query->whereHas('tahunAnggaran', function ($q) use ($filters) {
                    $q->where('tahun', $filters['tahun']);
                });
            }

            // Filter by bulan
            if (!empty($filters['bulan'])) {
                $query->where('bulan', $filters['bulan']);
            }

            // Filter by jenis kegiatan
            if (!empty($filters['jenis_kegiatan_id'])) {
                $query->where('jenis_kegiatan_id', $filters['jenis_kegiatan_id']);
            }

            $counts['total_kegiatan'] = $query->count();
        }

        return $counts;
    }

    /**
     * Get recent laporan for tables (additional data)
     */
    public function getRecentLaporan(array $filters = [], string $type = 'pending', int $limit = 5)
    {
        $user = Auth::user();
        $role = $this->determineUserRole($user);
        $petugas = $user->petugas;

        $query = DB::table('laporan_kegiatan as lk')
            ->select(
                'lk.id_laporan',
                'lk.status',
                'lk.tanggal_submit',
                'lk.tanggal_target',
                'd.nama_desa',
                'd.kode_desa',
                'kec.nama_kecamatan',
                'k.nama_kegiatan',
                'k.kode_kegiatan',
                'jk.nama_jenis'
            )
            ->leftJoin('desa as d', 'lk.desa_id', '=', 'd.id_desa')
            ->leftJoin('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->leftJoin('kegiatan as k', 'lk.kegiatan_id', '=', 'k.id_kegiatan')
            ->leftJoin('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->where('lk.status', $type === 'pending' ? 'submitted' : 'revision');

        // Apply role scope
        switch ($role) {
            case 'desa':
                $query->where('d.id_desa', $petugas->desa_id);
                break;
            case 'kecamatan':
                $query->where('d.kecamatan_id', $petugas->kecamatan_id);
                break;
        }

        // Apply filters
        if (!empty($filters['tahun'])) {
            $query->where('lk.tahun', $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $query->where('lk.bulan', $filters['bulan']);
        }

        return $query->orderBy('lk.tanggal_submit', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getUpcommingKegiatan(array $filters = [])
    {
        $user = Auth::user();
        $petugas = $user->petugas;

        // Dapatkan ID kecamatan
        $kecamatanId = $petugas ? $petugas->kecamatan_id : null;

        $query = DB::table('kegiatan as k')
            ->select(
                'k.id_kegiatan',
                'k.nama_kegiatan',
                'k.bulan',
                'ta.tahun',
                'jk.nama_jenis as jenis_kegiatan'
            )
            ->join('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->join('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->leftJoin('laporan_kegiatan as lk', function ($join) use ($kecamatanId, $filters) {
                $join->on('k.id_kegiatan', '=', 'lk.kegiatan_id');

                // Jika ada filter desa, join dengan desa tersebut
                if (!empty($filters['desa_id'])) {
                    $join->where('lk.desa_id', $filters['desa_id']);
                }
                // Jika tidak ada filter desa tapi ada kecamatan, join dengan semua desa di kecamatan
                elseif ($kecamatanId) {
                    $join->whereIn('lk.desa_id', function ($q) use ($kecamatanId) {
                        $q->select('id_desa')
                            ->from('desa')
                            ->where('kecamatan_id', $kecamatanId)
                            ->where('status', 'active');
                    });
                }

                // Filter tahun dan bulan di laporan
                if (!empty($filters['tahun'])) {
                    $join->where('lk.tahun', $filters['tahun']);
                }

                if (!empty($filters['bulan'])) {
                    $join->where('lk.bulan', $filters['bulan']);
                }
            })
            ->where('k.status', 'active')
            ->whereNull('lk.id_laporan'); // Hanya kegiatan yang belum dilaporkan

        // Filter tahun (dari tahun_anggaran)
        if (!empty($filters['tahun'])) {
            $query->where('ta.tahun', $filters['tahun']);
        }

        // Filter bulan (dari kegiatan)
        if (!empty($filters['bulan'])) {
            $query->where('k.bulan', $filters['bulan']);
        }

        // Filter jenis kegiatan
        if (!empty($filters['jenis_kegiatan_id'])) {
            $query->where('k.jenis_kegiatan_id', $filters['jenis_kegiatan_id']);
        }

        // Filter kecamatan (jika tidak ada filter desa)
        if (empty($filters['desa_id']) && $kecamatanId) {
            $query->whereExists(function ($q) use ($kecamatanId) {
                $q->select(DB::raw(1))
                    ->from('desa as d')
                    ->where('d.kecamatan_id', $kecamatanId)
                    ->where('d.status', 'active');
            });
        }

        return $query->orderBy('ta.tahun', 'desc')
            ->orderBy('k.bulan', 'desc')
            ->limit(10)
            ->get();
    }
}
