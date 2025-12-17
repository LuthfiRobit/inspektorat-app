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
            'kecamatan' => $request->filter_kecamatan
        ];

        $data = $this->scoringDesaService->getDesaData($filters);

        return DataTables::of($data)
            ->addColumn('aksi', function ($row) {
                return '<button class="btn btn-outline-primary btn-xs" onclick="showDesaDetail(' . $row->id_desa . ')">
                        <i class="fa fa-eye"></i> Detail
                    </button>';
            })
            ->addColumn('persentase_dokumen', function ($row) {
                return $row->persentase_dokumen_formatted;
            })
            ->addColumn('persentase_kegiatan', function ($row) {
                return $row->persentase_kegiatan_formatted;
            })
            ->addColumn('total_skor', function ($row) {
                return '<strong>' . $row->total_skor_formatted . '</strong>';
            })
            // ->addColumn('peringkat', function ($row) {
            //     return $row->peringkat_badge;
            // })
            ->rawColumns(['aksi', 'persentase_dokumen', 'persentase_kegiatan', 'total_skor'])
            // ->rawColumns(['aksi', 'persentase_dokumen', 'persentase_kegiatan', 'total_skor', 'peringkat'])
            ->make(true);
    }
}
