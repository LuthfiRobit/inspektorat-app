<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\ScoringDesaService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ScoringDesaController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $scoringDesaService;

    /**
     * ScoringDesaController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     * @param ScoringDesaService $scoringDesaService
     */
    public function __construct(
        ResponseService $responseService,
        TransactionService $transactionService,
        LogActivityService $logActivityService,
        ScoringDesaService $scoringDesaService
    ) {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->scoringDesaService = $scoringDesaService;
    }

    /**
     * Display the index view for Scoring Desa.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Scoring Desa');
        return view('administration.monitoring.scoring.desaIndex');
    }

    /**
     * Get Data for Scoring Desa DataTable
     *
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $filters = [
            'tahun' => $request->filter_tahun,
            'periode' => $request->filter_periode,
            'kecamatan' => $request->filter_kecamatan,
            'filter_desa' => $request->filter_desa
        ];

        // 1. Get ALL data first to determine global ranking (regardless of user role)
        // This ensures "Rank 5 of 100" is preserved even if we later filter the output to only 1 row.
        $allData = $this->scoringDesaService->getDesaData($filters);

        // 2. User Role Logic (Filter Output Collection)
        $user = auth()->user();
        $petugas = $user->petugas;
        $returnData = $allData;
        $userRankInfo = null;

        if ($petugas) {
            if ($petugas->desa_id) {
                // --- LOGIC FOR DESA USER ---
                $myDesaId = $petugas->desa_id;

                // Find my ranking in the global list
                $myRankIndex = $allData->search(function ($item) use ($myDesaId) {
                    return $item->id_desa == $myDesaId;
                });

                // User Rank Metadata
                $userRankInfo = [
                    'rank' => $myRankIndex !== false ? $myRankIndex + 1 : '-',
                    'total_desa' => $allData->count(),
                    'is_desa_user' => true
                ];

                // FORCE FILTER: Only return my desa row
                $returnData = $allData->filter(function ($item) use ($myDesaId) {
                    return $item->id_desa == $myDesaId;
                })->values();

            } elseif ($petugas->kecamatan_id) {
                // --- LOGIC FOR KECAMATAN USER ---
                $myKecamatanId = $petugas->kecamatan_id;

                // FORCE FILTER: Only return desas in my kecamatan
                $returnData = $allData->filter(function ($item) use ($myKecamatanId) {
                    return $item->kecamatan_id == $myKecamatanId;
                })->values();

                // Check if any desa in my kecamatan is ranked (optional metadata)
                $userRankInfo = [
                    'rank' => '-',
                    'total_desa' => $allData->count(),
                    'is_kecamatan_user' => true
                ];
            }
        }

        // If Inspektorat (no petugas/desa/kecamatan id), $returnData remains $allData (All Rows)

        return DataTables::of($returnData)
            ->with('userRanking', $userRankInfo)
            ->addColumn('aksi', function ($row) {
                return '<button class="btn btn-outline-primary btn-xs" onclick="showDesaDetail(' . $row->id_desa . ')">
                        <i class="fa fa-eye"></i> Detail
                    </button>';
            })
            // Update Columns
            // ->addColumn('persentase_dokumen', function ($row) {
            //     return $row->persentase_dokumen_formatted;
            // })
            ->addColumn('kegiatan_terlapor', function ($row) {
                return '<span class="badge badge-success">' . $row->kegiatan_terlapor . '</span>';
            })
            ->addColumn('kegiatan_belum_terlapor', function ($row) {
                return '<span class="badge badge-danger">' . $row->kegiatan_belum_terlapor . '</span>';
            })
            ->addColumn('persentase_kegiatan', function ($row) {
                return $row->persentase_kegiatan_formatted;
            })
            ->addColumn('jumlah_dokumen_wajib', function ($row) {
                return $row->jumlah_dokumen_wajib;
            })
            ->addColumn('jumlah_dokumen_tambahan', function ($row) {
                return $row->jumlah_dokumen_tambahan;
            })
            // ->addColumn('waktu_submit', function ($row) {
            //     return '<small>' . $row->earliest_submit_formatted . '</small>';
            // })
            ->addColumn('peringkat', function ($row) {
                return $row->peringkat_badge;
            })
            ->addColumn('total_skor', function ($row) {
                return '<strong>' . $row->total_skor_formatted . '</strong>';
            })
            // ->addColumn('peringkat', function ($row) {
            //     return $row->peringkat_badge;
            // })
            ->rawColumns(['aksi', 'kegiatan_terlapor', 'kegiatan_belum_terlapor', 'peringkat', 'total_skor'])
            // ->rawColumns(['aksi', 'persentase_dokumen', 'persentase_kegiatan', 'total_skor', 'peringkat'])
            ->make(true);
    }
    /**
     * Get Detail Scoring for specific Desa
     *
     * @param Request $request
     * @param int $id_desa
     * @return \Illuminate\View\View
     */
    public function detail(Request $request, $id_desa)
    {
        // --- AUTHORIZATION CHECK ---
        $user = auth()->user();
        $petugas = $user->petugas;

        if ($petugas) {
            if ($petugas->desa_id) {
                if ($petugas->desa_id != $id_desa) {
                    abort(403, 'Anda tidak memiliki akses untuk melihat detail desa ini.');
                }
            } elseif ($petugas->kecamatan_id) {
                // Check if desired desa is in my kecamatan
                $desaCheck = \App\Models\Desa::where('id_desa', $id_desa)
                    ->where('kecamatan_id', $petugas->kecamatan_id)
                    ->exists();
                if (!$desaCheck) {
                    abort(403, 'Anda tidak memiliki akses untuk melihat detail desa di luar kecamatan Anda.');
                }
            }
        }

        $filters = [
            'tahun' => $request->tahun,
            'periode' => $request->periode
        ];

        $data = $this->scoringDesaService->getDesaDetailData($id_desa, $filters);

        return view('administration.monitoring.scoring.detail', [
            'desa' => $data->desa,
            'details' => $data->details,
            'summary' => $data->summary,
            'filters' => $filters
        ]);
    }
}
