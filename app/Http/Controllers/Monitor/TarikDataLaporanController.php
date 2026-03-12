<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use App\Repositories\LaporanKegiatanRepository;
use App\Exports\LaporanKegiatanExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class TarikDataLaporanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $laporanRepository;

    /**
     * TarikDataLaporanController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     * @param LaporanKegiatanRepository $laporanRepository
     */
    public function __construct(
        ResponseService $responseService,
        TransactionService $transactionService,
        LogActivityService $logActivityService,
        LaporanKegiatanRepository $laporanRepository
    ) {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->laporanRepository = $laporanRepository;
    }

    /**
     * Display the index view for Tarik Data Laporan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Tarik Data Laporan');
        return view('administration.monitoring.tarikData.index');
    }

    /**
     * Retrieve and return the list of Tarik Data for DataTables
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $filters = [
            'filter_tahun' => $request->input('tahun', ''),
            'filter_bulan_awal' => $request->input('bulan_awal', ''),
            'filter_bulan_akhir' => $request->input('bulan_akhir', ''),
            'filter_status' => $request->input('status', ''),
        ];

        // Jika filter wajib kosong (awal load halaman), jangan fetch data.
        if (empty($filters['filter_tahun']) || empty($filters['filter_bulan_awal']) || empty($filters['filter_status'])) {
            return DataTables::of(collect())->make(true);
        }

        $data = $this->laporanRepository->getTarikDataList($user, $filters);

        $this->logActivityService->log('Fetched Tarik Data list. Filters: ' . json_encode($filters));

        return DataTables::of($data)
            ->editColumn('status_display', function ($row) {
                $statusClass = $row['status_class'] ?? 'light badge';
                return '<span class="badge ' . $statusClass . '">'
                    . ($row['status_display'] ?? $row['status']) .
                    '</span>';
            })
            ->editColumn('bulan', function ($row) {
                $bulan = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'Mei',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Ags',
                    9 => 'Sep',
                    10 => 'Okt',
                    11 => 'Nov',
                    12 => 'Des'
                ];
                return $bulan[$row['bulan']] ?? $row['bulan'];
            })
            ->rawColumns(['status_display'])
            ->make(true);
    }

    /**
     * Export the filtered data to Excel.
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $filters = [
            'filter_tahun' => $request->input('tahun', ''),
            'filter_bulan_awal' => $request->input('bulan_awal', ''),
            'filter_bulan_akhir' => $request->input('bulan_akhir', ''),
            'filter_status' => $request->input('status', ''),
        ];

        if (empty($filters['filter_tahun']) || empty($filters['filter_bulan_awal']) || empty($filters['filter_status'])) {
            return redirect()->back()->with('error', 'Silakan pilih filter yang wajib diisi.');
        }

        $data = $this->laporanRepository->getTarikDataList($user, $filters);

        $this->logActivityService->log('Exported Tarik Data to Excel. Filters: ' . json_encode($filters));

        // Format human-readable filter details for the Excel header
        $tahunAnggaran = \Illuminate\Support\Facades\DB::table('tahun_anggaran')
            ->where('id_tahun_anggaran', $filters['filter_tahun'])->first();

        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $periode = $bulanNama[$filters['filter_bulan_awal']] ?? '-';
        if (!empty($filters['filter_bulan_akhir']) && $filters['filter_bulan_akhir'] != $filters['filter_bulan_awal']) {
            $periode .= ' - ' . ($bulanNama[$filters['filter_bulan_akhir']] ?? '-');
        }

        $filterTexts = [
            'Tahun Anggaran' => $tahunAnggaran ? $tahunAnggaran->tahun : '-',
            'Periode Bulan' => $periode,
            'Status Laporan' => \App\Models\LaporanKegiatan::getStatusDisplayForUser($filters['filter_status']),
        ];

        $filename = 'Penarikan_Data_Laporan_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new LaporanKegiatanExport($data, $filterTexts), $filename);
    }
}
