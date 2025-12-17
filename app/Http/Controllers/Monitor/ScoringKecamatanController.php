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

        $data = $this->scoringDesaService->getKecamatanData($filters);

        return DataTables::of($data)
            ->addColumn('aksi', function ($row) {
                return '<button class="btn btn-outline-primary btn-xs" onclick="showKecamatanDetail(' . $row->id_kecamatan . ')">
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
            ->addColumn('rata_kegiatan', function ($row) {
                return $row->rata_kegiatan_formatted;
            })
            // ->addColumn('peringkat', function ($row) {
            //     return $row->peringkat_badge;
            // })
            // ->rawColumns(['aksi', 'persentase_dokumen', 'persentase_kegiatan', 'total_skor', 'peringkat'])
            ->rawColumns(['aksi', 'persentase_dokumen', 'persentase_kegiatan', 'total_skor'])
            ->make(true);
    }
}
