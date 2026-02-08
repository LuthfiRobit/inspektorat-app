<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\ScoringDesaService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ScoringKecamatanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $scoringDesaService;

    /**
     * ScoringKecamatanController constructor.
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
     * Display the index view for Scoring Kecamatan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Scoring Kecamatan');
        return view('administration.monitoring.scoring.kecamatanIndex');
    }

    /**
     * Get Data for Scoring Kecamatan DataTable
     *
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $filters = [
            'tahun' => $request->filter_tahun,
            'periode' => $request->filter_periode
        ];

        // 1. Get ALL data (already sorted by rank from service) to determine global ranking
        $allData = $this->scoringDesaService->getKecamatanData($filters);

        // 2. User Role Logic (Filter Output Collection)
        $user = auth()->user();
        $petugas = $user->petugas;
        $returnData = $allData;
        $userRankInfo = null;

        if ($petugas && $petugas->kecamatan_id) {
            // --- LOGIC FOR KECAMATAN USER ---
            $myKecamatanId = $petugas->kecamatan_id;

            // Search in collection
            $myRankIndex = $allData->search(function ($item) use ($myKecamatanId) {
                return $item->id_kecamatan == $myKecamatanId;
            });

            // Metadata Ranking
            $userRankInfo = [
                'rank' => $myRankIndex !== false ? $myRankIndex + 1 : '-',
                'total_kecamatan' => $allData->count(),
                'is_kecamatan_user' => true
            ];

            // FORCE FILTER: Only return my kecamatan row
            $returnData = $allData->filter(function ($item) use ($myKecamatanId) {
                return $item->id_kecamatan == $myKecamatanId;
            })->values();
        }

        // If Inspektorat or Desa User (Desa user logic for kecamatan scoring usually sees own kecamatan, 
        // but here we focus on Kecamatan user restriction. If Desa user accesses this, they might see all or restricted.
        // Assuming strictly Kecamatan/Inspektorat view for now based on request "jika scope user dalah kecamatan...")

        return DataTables::of($returnData)
            ->with('userRanking', $userRankInfo)
            ->addColumn('aksi', function ($row) {
                return '<button class="btn btn-outline-primary btn-xs" onclick="showKecamatanDetail(' . $row->id_kecamatan . ')">
                        <i class="fa fa-eye"></i> Detail
                    </button>';
            })
            // ->addColumn('persentase_dokumen', function ($row) {
            //     return $row->persentase_dokumen_formatted;
            // })
            ->addColumn('kegiatan_terlapor', function ($row) {
                return '<span class="badge badge-success">' . $row->kegiatan_terlapor . '</span>';
            })
            ->addColumn('kegiatan_belum_terlapor', function ($row) {
                return '<span class="badge badge-danger">' . $row->kegiatan_belum_terlapor . '</span>';
            })
            // ->addColumn('persentase_kegiatan', function ($row) {
            //     return $row->persentase_kegiatan_formatted;
            // })
            ->addColumn('total_skor', function ($row) {
                return '<strong>' . $row->total_skor_formatted . '</strong>';
            })
            ->addColumn('rata_kegiatan', function ($row) {
                return $row->rata_kegiatan_formatted;
            })
            ->addColumn('peringkat', function ($row) {
                return $row->peringkat_badge;
            })
            ->rawColumns(['aksi', 'kegiatan_terlapor', 'kegiatan_belum_terlapor', 'total_skor', 'peringkat'])
            ->make(true);
    }
    /**
     * Get Detail Scoring for specific Kecamatan
     *
     * @param Request $request
     * @param int $id_kecamatan
     * @return \Illuminate\View\View
     */
    public function detail(Request $request, $id_kecamatan)
    {
        // --- AUTHORIZATION CHECK ---
        $user = auth()->user();
        $petugas = $user->petugas;

        if ($petugas && $petugas->kecamatan_id) {
            if ($petugas->kecamatan_id != $id_kecamatan) {
                abort(403, 'Anda tidak memiliki akses untuk melihat detail kecamatan ini.');
            }
        }

        $filters = [
            'tahun' => $request->tahun,
            'periode' => $request->periode
        ];

        $data = $this->scoringDesaService->getKecamatanDetailData($id_kecamatan, $filters);

        return view('administration.monitoring.scoring.detail-kecamatan', [
            'kecamatan' => $data->kecamatan,
            'desas' => $data->desas,
            'summary' => $data->summary,
            'filters' => $filters
        ]);
    }
}
