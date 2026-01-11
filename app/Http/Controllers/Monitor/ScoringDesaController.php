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

        // 1. Get ALL data first to determine global ranking
        $allData = $this->scoringDesaService->getDesaData($filters);

        // 2. Data to be returned (Default: All Data)
        $returnData = $allData;
        $userRankInfo = null;

        // 3. User Role Logic
        $user = auth()->user();
        $petugas = $user->petugas;

        if ($petugas && $petugas->desa_id) {
            // --- LOGIC FOR DESA USER ---
            $myDesaId = $petugas->desa_id;

            // Search in collection (already sorted by rank)
            $myRankIndex = $allData->search(function ($item) use ($myDesaId) {
                return $item->id_desa == $myDesaId;
            });

            if ($myRankIndex !== false) {
                $userRankInfo = [
                    'rank' => $myRankIndex + 1,
                    'total_desa' => $allData->count(),
                    'is_desa_user' => true
                ];
            } else {
                $userRankInfo = [
                    'rank' => '-',
                    'total_desa' => $allData->count(),
                    'is_desa_user' => true
                ];
            }

            // Limit to Top 5
            $returnData = $allData->take(5);
        }

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
            ->addColumn('waktu_submit', function ($row) {
                return '<small>' . $row->earliest_submit_formatted . '</small>';
            })
            ->addColumn('peringkat', function ($row) {
                return $row->peringkat_badge;
            })
            ->addColumn('total_skor', function ($row) {
                return '<strong>' . $row->total_skor_formatted . '</strong>';
            })
            // ->addColumn('peringkat', function ($row) {
            //     return $row->peringkat_badge;
            // })
            ->rawColumns(['aksi', 'kegiatan_terlapor', 'kegiatan_belum_terlapor', 'waktu_submit', 'peringkat', 'total_skor'])
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
