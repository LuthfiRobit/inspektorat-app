<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Petugas;
use App\Models\PetugasWilayahBinaan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class WilayahBinaanController extends Controller
{
    protected $responseService;
    protected $logActivityService;

    public function __construct(ResponseService $responseService, LogActivityService $logActivityService)
    {
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $user = Auth::user();
        $petugas = $user->petugas;
        $scope = 'inspektorat';

        if ($petugas && !empty($petugas->kecamatan_id) && empty($petugas->desa_id)) {
            $scope = 'kecamatan';
        } elseif ($petugas && !empty($petugas->desa_id)) {
            abort(403, 'Akses ditolak.');
        }

        $this->logActivityService->log('Accessed Wilayah Binaan index view');
        return view('administration.masters.wilayahBinaan.index', compact('scope'));
    }

    public function listInspektorat(Request $request)
    {
        $query = Petugas::withCount('kecamatanBinaan')
            ->whereNull('kecamatan_id')
            ->whereNull('desa_id')
            ->where('status', 'active');

        return DataTables::of($query)
            ->addColumn('aksi', function ($row) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i> Aksi
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="' . route('administrator.master.wilayah-binaan.show', $row->id_petugas) . '">
                                    <i class="fas fa-edit"></i> Kelola Binaan
                                </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function listKecamatan(Request $request)
    {
        $query = Petugas::withCount('desaBinaan')
            ->with('kecamatan')
            ->whereNotNull('kecamatan_id')
            ->whereNull('desa_id')
            ->where('status', 'active');

        $user = Auth::user();
        $petugas = $user->petugas;
        $scope = 'inspektorat';
        if ($petugas && !empty($petugas->kecamatan_id) && empty($petugas->desa_id)) {
            $scope = 'kecamatan';
        }

        if ($scope === 'kecamatan') {
            $query->where('kecamatan_id', $petugas->kecamatan_id);
        } else {
            if ($request->has('kecamatan_id') && $request->kecamatan_id) {
                $query->where('kecamatan_id', $request->kecamatan_id);
            }
        }

        return DataTables::of($query)
            ->addColumn('aksi', function ($row) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i> Aksi
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="' . route('administrator.master.wilayah-binaan.show', $row->id_petugas) . '">
                                    <i class="fas fa-edit"></i> Kelola Binaan
                                </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show($id)
    {
        $petugas = Petugas::with(['kecamatanBinaan', 'desaBinaan', 'kecamatan'])->findOrFail($id);

        $user = Auth::user();
        $userPetugas = $user->petugas;
        if ($userPetugas && !empty($userPetugas->kecamatan_id) && empty($userPetugas->desa_id)) {
            if ($petugas->kecamatan_id != $userPetugas->kecamatan_id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $this->logActivityService->log('Viewed Wilayah Binaan details', ['petugas_id' => $id]);

        $type = (is_null($petugas->kecamatan_id) && is_null($petugas->desa_id)) ? 'inspektorat' : 'kecamatan';

        if ($type === 'inspektorat') {
            $assignedIds = $petugas->kecamatanBinaan->pluck('id_kecamatan')->toArray();
            $available = Kecamatan::where('status', 'active')
                ->whereNotIn('id_kecamatan', PetugasWilayahBinaan::whereNotNull('kecamatan_id')->pluck('kecamatan_id')->toArray())
                ->get();
        } else {
            $assignedIds = $petugas->desaBinaan->pluck('id_desa')->toArray();
            $available = Desa::where('status', 'active')
                ->where('kecamatan_id', $petugas->kecamatan_id)
                ->whereNotIn('id_desa', PetugasWilayahBinaan::whereNotNull('desa_id')->pluck('desa_id')->toArray())
                ->get();
        }

        return view('administration.masters.wilayahBinaan.show', compact('petugas', 'type', 'available'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'petugas_id' => 'required|exists:petugas,id_petugas',
            'type' => 'required|in:inspektorat,kecamatan',
            'wilayah_ids' => 'required|array',
        ]);

        $petugas = Petugas::findOrFail($request->petugas_id);

        $user = Auth::user();
        $userPetugas = $user->petugas;
        if ($userPetugas && !empty($userPetugas->kecamatan_id) && empty($userPetugas->desa_id)) {
            if ($petugas->kecamatan_id != $userPetugas->kecamatan_id) {
                return $this->responseService->error('Akses ditolak.');
            }
        }

        DB::beginTransaction();
        try {
            if ($request->type === 'inspektorat') {
                foreach ($request->wilayah_ids as $kecamatan_id) {
                    $existing = PetugasWilayahBinaan::where('kecamatan_id', $kecamatan_id)->first();
                    if ($existing && $existing->petugas_id != $petugas->id_petugas) {
                        DB::rollBack();
                        return $this->responseService->error("Kecamatan ID {$kecamatan_id} sudah dibina oleh petugas lain.");
                    }
                    
                    PetugasWilayahBinaan::firstOrCreate([
                        'petugas_id' => $petugas->id_petugas,
                        'kecamatan_id' => $kecamatan_id
                    ]);
                }
            } else {
                foreach ($request->wilayah_ids as $desa_id) {
                    $desa = Desa::findOrFail($desa_id);
                    if ($desa->kecamatan_id != $petugas->kecamatan_id) {
                        DB::rollBack();
                        return $this->responseService->error("Desa {$desa->nama_desa} tidak berada dalam kecamatan petugas.");
                    }

                    $existing = PetugasWilayahBinaan::where('desa_id', $desa_id)->first();
                    if ($existing && $existing->petugas_id != $petugas->id_petugas) {
                        DB::rollBack();
                        return $this->responseService->error("Desa {$desa->nama_desa} sudah dibina oleh petugas lain.");
                    }

                    PetugasWilayahBinaan::firstOrCreate([
                        'petugas_id' => $petugas->id_petugas,
                        'desa_id' => $desa_id
                    ]);
                }
            }

            DB::commit();
            $this->logActivityService->log('Assigned wilayah binaan to petugas', ['petugas_id' => $petugas->id_petugas]);

            return $this->responseService->success(null, 'Wilayah binaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseService->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getAssignedAndAvailable($id)
    {
        $petugas = Petugas::with(['kecamatanBinaan', 'desaBinaan', 'kecamatan'])->findOrFail($id);

        $user = Auth::user();
        $userPetugas = $user->petugas;
        if ($userPetugas && !empty($userPetugas->kecamatan_id) && empty($userPetugas->desa_id)) {
            if ($petugas->kecamatan_id != $userPetugas->kecamatan_id) {
                return $this->responseService->error('Akses ditolak.');
            }
        }

        $type = (is_null($petugas->kecamatan_id) && is_null($petugas->desa_id)) ? 'inspektorat' : 'kecamatan';

        $assigned = [];
        if ($type === 'inspektorat') {
            $binaanList = $petugas->kecamatanBinaan;
            $available = Kecamatan::where('status', 'active')
                ->whereNotIn('id_kecamatan', PetugasWilayahBinaan::whereNotNull('kecamatan_id')->pluck('kecamatan_id')->toArray())
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id_kecamatan,
                        'nama' => $item->nama_kecamatan
                    ];
                });

            foreach ($binaanList as $binaan) {
                $pivot = PetugasWilayahBinaan::where('petugas_id', $petugas->id_petugas)
                    ->where('kecamatan_id', $binaan->id_kecamatan)
                    ->first();
                $assigned[] = [
                    'pivot_id' => $pivot ? $pivot->id : null,
                    'nama' => $binaan->nama_kecamatan
                ];
            }
        } else {
            $binaanList = $petugas->desaBinaan;
            $available = Desa::where('status', 'active')
                ->where('kecamatan_id', $petugas->kecamatan_id)
                ->whereNotIn('id_desa', PetugasWilayahBinaan::whereNotNull('desa_id')->pluck('desa_id')->toArray())
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id_desa,
                        'nama' => $item->nama_desa
                    ];
                });

            foreach ($binaanList as $binaan) {
                $pivot = PetugasWilayahBinaan::where('petugas_id', $petugas->id_petugas)
                    ->where('desa_id', $binaan->id_desa)
                    ->first();
                $assigned[] = [
                    'pivot_id' => $pivot ? $pivot->id : null,
                    'nama' => $binaan->nama_desa
                ];
            }
        }

        return $this->responseService->success([
            'assigned' => $assigned,
            'available' => $available
        ], 'Data retrieved successfully.');
    }

    public function destroy($id)
    {
        try {
            $wilayah = PetugasWilayahBinaan::findOrFail($id);
            $petugasId = $wilayah->petugas_id;
            $wilayah->delete();

            $this->logActivityService->log('Removed wilayah binaan from petugas', ['petugas_id' => $petugasId]);

            return $this->responseService->success(null, 'Wilayah binaan berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus wilayah binaan.');
        }
    }
}
