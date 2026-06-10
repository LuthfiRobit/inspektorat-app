<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\LaporanKegiatan;
use App\Services\FileUploadService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class RiwayatLaporanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $fileUploadService;

    /**
     * RiwayatLaporanController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(
        ResponseService $responseService,
        TransactionService $transactionService,
        LogActivityService $logActivityService,
        FileUploadService $fileUploadService
    ) {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display the index view for Riwayat Laporan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Riwayat Laporan');
        return view('administration.monitoring.riwayat.index');
    }

    /**
     * Retrieve and return the list of Riwayat Laporan for DataTables
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tahun' => $request->input('filter_tahun', ''),
            'filter_periode' => $request->input('filter_periode', ''),
            'filter_kegiatan' => $request->input('filter_kegiatan', ''),
            'filter_desa' => $request->input('filter_desa', ''),
            'search' => $request->input('search', ''),
        ];

        $data = LaporanKegiatan::getListHistory($user, $filters);

        $this->logActivityService->log('Fetched Laporan Kegiatan History', [
            'user_id' => $user->id_user,
            'filters' => $filters
        ]);

        return DataTables::of($data)

            ->addColumn('aksi', function ($row) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $btnDetail = '';
                
                if ($user && $user->hasPermissionTo('monitoring.riwayat.view')) {
                    $url = route('administrator.monitoring.riwayat.detail')
                        . '?desa_id=' . $row['desa_id']
                        . '&kegiatan_id=' . $row['kegiatan_id']
                        . '&id_laporan=' . $row['id_laporan'];

                    $btnDetail = '
                    <a class="dropdown-item" href="' . $url . '">
                        <i class="fas fa-eye me-2"></i>Detail Laporan
                    </a>';
                }

                if ($btnDetail === '') {
                    return '-';
                }

                return '
                <div class="btn-group">
                    <button type="button" 
                            class="btn btn-outline-primary btn-xs dropdown-toggle" 
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs"></i> Aksi
                    </button>
                    <div class="dropdown-menu">
                        ' . $btnDetail . '
                    </div>
                </div>';
            })

            ->editColumn('status_display', function ($row) {
                $badgeClass = [
                    'success' => 'light badge-success',
                    'primary' => 'light badge-primary',
                    'warning' => 'light badge-warning',
                    'danger' => 'light badge-danger',
                    'secondary' => 'light badge-secondary',
                    'light' => 'light badge-dark',
                ][$row['status_class']] ?? 'light badge';

                return '<span class="badge ' . $badgeClass . '">'
                    . $row['status_display'] .
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

            ->editColumn('nama_kegiatan', function ($row) {
                if (strlen($row['nama_kegiatan']) > 40) {
                    return '<span data-bs-toggle="tooltip" title="' . e($row['nama_kegiatan']) . '">'
                        . substr($row['nama_kegiatan'], 0, 40) . '...</span>';
                }
                return $row['nama_kegiatan'];
            })

            ->editColumn('nama_desa', function ($row) {
                return $row['nama_desa']
                    . ' <br><small class="text-muted">'
                    . $row['nama_kecamatan']
                    . '</small>';
            })

            ->addColumn('timeline', function ($row) {

                $badgeClass = [
                    'Terlambat' => 'light badge-danger',
                    'Tenggang' => 'light badge-warning',
                    'Menunggu' => 'light badge-success',
                    'Tepat Waktu' => 'light badge-primary',
                ][$row['timeline_status']] ?? 'light badge';

                $timelineInfo = '';

                if (!empty($row['tanggal_target'])) {
                    $timelineInfo .= '<small class="text-muted d-block" style="font-size: 0.7rem;">
                                    Target: ' . \Carbon\Carbon::parse($row['tanggal_target'])->format('d M Y') . '
                                  </small>';
                }

                if (!empty($row['tanggal_submit'])) {
                    $timelineInfo .= '<small class="text-muted d-block" style="font-size: 0.7rem;">
                                    Submit: ' . \Carbon\Carbon::parse($row['tanggal_submit'])->format('d M Y') . '
                                  </small>';
                }

                if (!empty($row['tanggal_approve'])) {
                    $timelineInfo .= '<small class="text-success d-block" style="font-size: 0.7rem;">
                                    Approve: ' . \Carbon\Carbon::parse($row['tanggal_approve'])->format('d M Y') . '
                                  </small>';
                }

                return '
                <div class="text-center">
                    <span class="badge ' . $badgeClass . ' mb-1">'
                    . $row['timeline_status'] .
                    '</span>
                    <br>' . $timelineInfo . '
                </div>';
            })

            ->rawColumns(['aksi', 'status_display', 'timeline', 'nama_kegiatan', 'nama_desa'])
            ->make(true);
    }


    /**
     * Display the detail view for existing History Laporan.
     */
    public function detail(Request $request)
    {
        $this->logActivityService->log('Accessed detail view for Laporan Kegiatan History');

        $desa_id = $request->query('desa_id');
        $kegiatan_id = $request->query('kegiatan_id');
        $id_laporan = $request->query('id_laporan');

        return view('administration.monitoring.riwayat.detail', compact('desa_id', 'kegiatan_id', 'id_laporan'));
    }

    /**
     * Get detailed data for history laporan
     */
    public function getData($id)
    {
        try {
            $data = LaporanKegiatan::getCompleteHistoryData($id);

            if (!$data) {
                return $this->responseService->error('Data laporan tidak ditemukan');
            }

            Log::info("History detail data sent to frontend for laporan $id", [
                'completeness' => $data['completeness'] . '%',
                'status' => $data['status']
            ]);

            return $this->responseService->success($data);
        } catch (\Exception $e) {
            Log::error('Error getting history detail data: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat mengambil data detail history');
        }
    }
}
