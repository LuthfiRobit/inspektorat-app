<?php

namespace App\Http\Controllers\Monev;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * LaporanController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService,  LogActivityService $logActivityService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
    }

    /**
     * Display the index view for Monev Laporan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Jadwal Laporan');
        return view('administration.monev.laporan.index');
    }


    public function list(Request $request)
    {
        // Ambil kecamatan dari user login
        // $kecamatanId = DB::table('petugas')
        //     ->where('user_id', auth()->id())
        //     ->value('kecamatan_id');

        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tahun' => $request->input('filter_tahun', ''),
            'filter_bulan' => $request->input('filter_bulan', ''),
            'filter_desa' => $request->input('filter_desa', ''),
            'search' => $request->input('search', ''),
            'filter_kecamatan' => 3, // ditambahkan untuk filtering di bawah
        ];

        $query = LaporanKegiatan::getFilters($filters);

        $this->logActivityService->log('Fetched Laporan Kegiatan list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_laporan . '" name="laporan_ids[]" value="' . $row->id_laporan . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs"></i> Aksi
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_laporan . '">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_laporan . '">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = [
                    'draft' => 'badge-secondary',
                    'submitted' => 'badge-warning',
                    'revision' => 'badge-info',
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger'
                ][$item->status] ?? 'badge-secondary';

                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->editColumn('periode', function ($item) {
                $bulan = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'Mei',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Agu',
                    9 => 'Sep',
                    10 => 'Okt',
                    11 => 'Nov',
                    12 => 'Des'
                ][$item->bulan] ?? 'Unknown';

                return $bulan . ' ' . $item->tahun;
            })
            ->editColumn('keterlambatan', function ($item) {
                if ($item->status === 'submitted' && $item->tanggal_submit > $item->tanggal_target) {
                    return '<span class="badge badge-danger">Terlambat</span>';
                }
                return '<span class="badge badge-success">Tepat Waktu</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'periode', 'keterlambatan'])
            ->make(true);
    }

    /**
     * Retrieve and return the list of Laporan Kegiatan for DataTables
     */
    public function list(Request $request)
    {
        Auth::loginUsingId(3); // atau Auth::login(User::find(1));

        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tahun' => $request->input('filter_tahun', ''),
            'filter_desa' => $request->input('filter_desa', ''),
            'search' => $request->input('search', ''),
        ];

        $query = LaporanKegiatan::getListForUser($user, $filters);

        $this->logActivityService->log('Fetched Laporan Kegiatan list for User', [
            'user_id' => $user->id_user,
            'filters' => $filters
        ]);

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                $isDisabled = in_array($row->status, ['submitted', 'approved', 'rejected']) ? 'disabled' : '';
                return '<input type="checkbox" class="table-checkbox form-check-input" ' . $isDisabled .
                    ' id="checkbox_' . $row->id_laporan . '" name="laporan_ids[]" value="' . $row->id_laporan . '">';
            })
            ->addColumn('aksi', function ($row) {
                $btnDetail = '<a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $row->id_laporan . '">
                                <i class="fas fa-eye me-2"></i>Detail
                              </a>';

                // Tombol Laporkan hanya untuk yang belum dilaporkan, draft, atau revision
                $btnLaporkan = '';
                if (in_array($row->status, [null, '', 'draft', 'revision'])) {
                    $btnLaporkan = '<a class="dropdown-item" href="' . route('administrator.monev.laporan.create', $row->id_laporan) . '">
                                      <i class="fas fa-edit me-2"></i>Laporkan
                                    </a>';
                } else {
                    $btnLaporkan = '<a class="dropdown-item disabled" href="javascript:void(0);">
                                      <i class="fas fa-check me-2"></i>Sudah Dilaporkan
                                    </a>';
                }

                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i> Aksi
                            </button>
                            <div class="dropdown-menu">
                                ' . $btnDetail . '
                                ' . $btnLaporkan . '
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
                    'light' => 'light badge'
                ][$row->status_class] ?? 'light badge';

                return '<span class="badge ' . $badgeClass . '">' . $row->status_display . '</span>';
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
                return $bulan[$row->bulan] ?? $row->bulan;
            })
            ->editColumn('nama_kegiatan', function ($row) {
                // Batasi 40 karakter untuk nama kegiatan
                if (strlen($row->nama_kegiatan) > 40) {
                    return '<span data-bs-toggle="tooltip" title="' . e($row->nama_kegiatan) . '">'
                        . substr($row->nama_kegiatan, 0, 40) . '...</span>';
                }
                return $row->nama_kegiatan;
            })
            ->addColumn('timeline', function ($row) {
                $badgeClass = [
                    'Terlambat' => 'light badge-danger',
                    'Tenggang' => 'light badge-warning',
                    'Aman' => 'light badge-success',
                    'Tepat Waktu' => 'light badge-primary'
                ][$row->timeline_status] ?? 'light badge';

                return '<div class="text-center">
                          <span class="badge ' . $badgeClass . ' mb-1">' . $row->timeline_status . '</span>
                          <br>
                          <small class="text-muted" style="font-size: 0.7rem;">' . $row->timeline_dates . '</small>
                        </div>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status_display', 'timeline', 'nama_kegiatan'])
            ->make(true);
    }

    public function show($id)
    {
        // $laporan = LaporanKegiatan::getWithRelationships($id);

        // if (!$laporan) {
        //     $this->logActivityService->log('Laporan Kegiatan not found for show', ['id' => $id]);
        //     return $this->responseService->error('Data laporan tidak ditemukan', ResponseService::STATUS_NOT_FOUND);
        // }

        // // Get pertanyaan for this kegiatan
        // $pertanyaan = DB::table('pertanyaan_kegiatan')
        //     ->where('kegiatan_id', $laporan->kegiatan_id)
        //     ->where('status', 'active')
        //     ->orderBy('urutan', 'ASC')
        //     ->get(['id_pertanyaan', 'urutan', 'pertanyaan']);

        // $data = [
        //     'laporan' => $laporan,
        //     'pertanyaan' => $pertanyaan
        // ];

        $kegiatan = Kegiatan::getRelationship($id);
        if (!$kegiatan) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $pertanyaan = PertanyaanKegiatan::getByKegiatan($id);

        $laporan = LaporanKegiatan::where('kegiatan_id', $id)->first();

        $data = [
            'kegiatan' => $kegiatan,
            'pertanyaan' => $pertanyaan,
            'laporan' => $laporan
        ];

        $this->logActivityService->log('Viewed Kegiatan dan Its Laporan detail', ['id' => $id]);
        return $this->responseService->success($data, 'Data laporan berhasil diambil');
    }


    //untuk model
    public static function getFilters(array $filters = [])
    {
        $query = DB::table('laporan_kegiatan as lk')
            ->select(
                'lk.id_laporan',
                'lk.tahun',
                'lk.bulan',
                'lk.status',
                'lk.tanggal_target',
                'lk.tanggal_submit',
                'lk.tanggal_approve',
                'lk.created_at',
                'd.nama_desa',
                'k.nama_kegiatan',
                'k.kode_kegiatan',
                'u.name as created_by_name'
            )
            ->leftJoin('desa as d', 'lk.desa_id', '=', 'd.id_desa')
            ->rightJoin('kegiatan as k', 'lk.kegiatan_id', '=', 'k.id_kegiatan')
            ->leftJoin('users as u', 'lk.created_by', '=', 'u.id_user');

        // === Filtering ===
        if (!empty($filters['filter_kecamatan'])) {
            $query->whereIn('lk.desa_id', function ($sub) use ($filters) {
                $sub->select('id_desa')
                    ->from('desa')
                    ->where('kecamatan_id', $filters['filter_kecamatan']);
            });
        }

        if (!empty($filters['filter_status'])) {
            $query->where('lk.status', $filters['filter_status']);
        }

        if (!empty($filters['filter_tahun'])) {
            $query->where('lk.tahun', $filters['filter_tahun']);
        }

        if (!empty($filters['filter_bulan'])) {
            $query->where('lk.bulan', $filters['filter_bulan']);
        }

        if (!empty($filters['filter_desa'])) {
            $query->where('lk.desa_id', $filters['filter_desa']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('d.nama_desa', 'like', "%{$search}%")
                    ->orWhere('k.nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('k.kode_kegiatan', 'like', "%{$search}%");
            });
        }

        // === Prioritaskan laporan berdasarkan status dan tanggal_target ===
        $query->orderByRaw("
        FIELD(lk.status, 'draft', 'submitted', 'revision', 'rejected', 'approved'),
        lk.tanggal_target ASC,
        lk.created_at DESC");

        return $query->get();
    }
}
