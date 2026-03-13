<?php

namespace App\Http\Controllers\Monev;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kegiatan;
use App\Models\LaporanKegiatan;
use App\Models\PertanyaanKegiatan;
use App\Services\LaporanKegiatanService;
use App\Services\ResponseService;
use App\ServicesLogActivityService;
use App\Repositories\LaporanKegiatanRepository;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LaporanKegiatanController extends Controller
{
    protected $responseService;
    protected $logActivityService;
    protected $laporanKegiatanService;
    protected $laporanRepository;

    public function __construct(
        ResponseService $responseService,
        LogActivityService $logActivityService,
        LaporanKegiatanService $laporanKegiatanService,
        LaporanKegiatanRepository $laporanRepository
    ) {
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
        $this->laporanKegiatanService = $laporanKegiatanService;
        $this->laporanRepository = $laporanRepository;
    }

    /**
     * Display the index view for Laporan Kegiatan.
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Laporan Kegiatan');
        return view('administration.monev.buatLaporan.index');
    }

    /**
     * Retrieve and return the list of Laporan Kegiatan for DataTables
     */
    public function list(Request $request)
    {
        // Auth::loginUsingId(1); // atau Auth::login(User::find(1));

        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tahun' => $request->input('filter_tahun', ''),
            'filter_periode' => $request->input('filter_periode', ''),
            'filter_desa' => $request->input('filter_desa', ''),
            'search' => $request->input('search', ''),
        ];

        $data = $this->laporanRepository->getListForUser($user, $filters);

        $this->logActivityService->log('Fetched Laporan Kegiatan list for User', [
            'user_id' => $user->id_user,
            'filters' => $filters
        ]);

        return DataTables::of($data)
            // ->addColumn('checkbox', function ($row) {
            //     $isDisabled = in_array($row['status'], ['submitted', 'approved', 'rejected', 'belum_dilaporkan']) ? 'disabled' : '';
            //     $value = $row['status'] === 'belum_dilaporkan' ? '' : ($row['id_laporan'] ?? '');
            //     return '<input type="checkbox" class="table-checkbox form-check-input" ' . $isDisabled .
            //         ' id="checkbox_' . ($row['id_laporan'] ?? 'new') . '" name="laporan_ids[]" value="' . $value . '">';
            // })
            ->addColumn('aksi', function ($row) {
                return $this->buildActionButtons($row);
            })
            ->editColumn('status_display', function ($row) {
                return $this->formatStatusBadge($row);
            })
            ->editColumn('bulan', function ($row) {
                return $this->formatBulan($row['bulan']);
            })
            ->editColumn('nama_kegiatan', function ($row) {
                return $this->formatNamaKegiatan($row['nama_kegiatan']);
            })
            ->editColumn('nama_desa', function ($row) {
                return $this->formatNamaDesa($row);
            })
            ->addColumn('timeline', function ($row) {
                return $this->formatTimeline($row);
            })
            ->rawColumns(['checkbox', 'aksi', 'status_display', 'timeline', 'nama_kegiatan', 'nama_desa'])
            ->make(true);
    }

    /**
     * Display the details of a specific Laporan Kegiatan
     */
    public function show($id)
    {
        $kegiatan = Kegiatan::getRelationship($id);
        if (!$kegiatan) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $data = [
            'kegiatan' => $kegiatan,
            'pertanyaan' => PertanyaanKegiatan::getByKegiatan($id),
            'laporan' => LaporanKegiatan::where('kegiatan_id', $id)->first()
        ];

        $this->logActivityService->log('Viewed Kegiatan dan Its Laporan detail', ['id' => $id]);
        return $this->responseService->success($data, 'Data laporan berhasil diambil');
    }

    /**
     * Get detail for modal detail Laporan Kegiatan
     *
     * Required request:
     * - desa_id
     * - kegiatan_id
     * - laporan_id (nullable)
     */
    public function showRequest(Request $request)
    {
        $data = $this->laporanRepository->getDetail(
            $request->desa_id,
            $request->kegiatan_id,
            $request->laporan_id,
            $request->bulan, // New Param
            $request->tahun  // New Param
        );

        $this->logActivityService->log('Viewed Detail Laporan');

        return $this->responseService->success($data, 'Detail berhasil diambil');
    }


    /**
     * Display the create view for Laporan Kegiatan.
     */
    public function create(Request $request)
    {
        $this->logActivityService->log('Accessed create view for Laporan Kegiatan');

        $desa_id = $request->query('desa_id');
        $kegiatan_id = $request->query('kegiatan_id');
        $id_laporan = $request->query('id_laporan');

        return view('administration.monev.buatLaporan.create-new', compact('desa_id', 'kegiatan_id', 'id_laporan'));
    }

    /**
     * Display the edit view for existing Laporan Kegiatan.
     */
    public function edit(Request $request)
    {
        $this->logActivityService->log('Accessed edit view for Laporan Kegiatan');

        $desa_id = $request->query('desa_id');
        $kegiatan_id = $request->query('kegiatan_id');
        $id_laporan = $request->query('id_laporan');

        return view('administration.monev.buatLaporan.edit', compact('desa_id', 'kegiatan_id', 'id_laporan'));
    }

    /**
     * Get laporan data for edit
     */
    public function getData($id)
    {
        $laporan = $this->laporanRepository->getWithRelationships($id);
        if (!$laporan) {
            return $this->responseService->error('Data tidak ditemukan');
        }

        $data = array_merge(['laporan' => $laporan], $this->laporanRepository->getJawabanWithDokumen($id));

        return $this->responseService->success($data);
    }

    /**
     * Get kegiatan data with questions and requirements
     */
    public function getKegiatanData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'desa_id' => 'required|exists:desa,id_desa',
            'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan'
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors());
        }

        $data = $this->laporanKegiatanService->getKegiatanData($request->desa_id, $request->kegiatan_id);

        if (!$data) {
            return $this->responseService->error('Data kegiatan tidak ditemukan', 404);
        }

        return $this->responseService->success($data, 'Data berhasil diambil');
    }

    /**
     * Store a new Laporan Kegiatan.
     */
    public function store(Request $request)
    {
        $validator = $this->validateLaporanRequest($request);
        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Laporan Kegiatan store');
            return $this->responseService->validationError($validator->errors());
        }

        try {
            $validatedData = $validator->validated();
            $laporan = $this->laporanKegiatanService->storeLaporan(
                $validatedData,
                $request->jawaban,
                $request->file('files', [])
            );

            $this->logActivityService->log('Stored new Laporan Kegiatan', 'Laporan ID: ' . $laporan->id_laporan);

            return $this->responseService->success(
                ['laporan_id' => $laporan->id_laporan],
                'Laporan berhasil disimpan'
            );
        } catch (\Exception $e) {
            Log::error('Error storing laporan: ' . $e->getMessage());
            return $this->responseService->error('Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing Laporan Kegiatan.
     */
    public function update(Request $request, $id)
    {
        $laporan = LaporanKegiatan::find($id);
        if (!$laporan) {
            $this->logActivityService->log('Laporan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data tidak ditemukan', ResponseService::STATUS_NOT_FOUND);
        }

        $validator = $this->validateLaporanRequest($request);
        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Laporan Kegiatan update');
            return $this->responseService->validationError($validator->errors());
        }

        try {
            $validatedData = $validator->validated();
            // Tambahkan created_by ke validated data
            // $validatedData['created_by'] = Auth::id();
            $this->laporanKegiatanService->updateLaporan(
                $id,
                $validatedData,
                $request->jawaban,
                $request->file('files', [])
            );

            $this->logActivityService->log('Updated Laporan Kegiatan', 'Laporan ID: ' . $id);

            return $this->responseService->success(
                ['laporan_id' => $id],
                'Laporan berhasil diupdate'
            );
        } catch (\Exception $e) {
            Log::error('Error updating laporan: ' . $e->getMessage());
            return $this->responseService->error('Gagal mengupdate laporan: ' . $e->getMessage());
        }
    }

    /**
     * Validation rules for laporan request
     */
    private function validateLaporanRequest(Request $request)
    {
        $rules = [
            'desa_id' => 'required|exists:desa,id_desa',
            'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'status' => 'required|in:draft,submitted',
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|in:sudah,belum',
            'files' => 'nullable|array',
            'files.*.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ];

        $messages = [
            'files.*.*.mimes' => 'Format dokumen harus berformat PDF, DOC, atau DOCX.',
            'files.*.*.max' => 'Ukuran dokumen maksimal adalah 2MB.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Build action buttons for datatable
     */
    private function buildActionButtons($row)
    {
        $btnDetail = '<a class="dropdown-item" href="javascript:void(0);" data-action="action_show" 
                            data-laporan-id="' . $row['id_laporan'] . '"
                            data-kegiatan-id="' . $row['kegiatan_id'] . '" 
                            data-desa-id="' . $row['desa_id'] . '" 
                            data-tahun="' . $row['tahun'] . '" 
                            data-bulan="' . $row['bulan'] . '"
                            data-id="' . $row['kegiatan_id'] . '">
                        <i class="fas fa-eye me-2"></i>Detail
                    </a>';

        $btnLaporkan = $this->buildLaporkanButton($row);

        return '<div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs"></i> Aksi
                    </button>
                    <div class="dropdown-menu">
                        ' . $btnDetail . '
                        ' . $btnLaporkan . '
                    </div>
                </div>';
    }

    /**
     * Build laporkan button based on status
     */
    private function buildLaporkanButton($row)
    {
        if (in_array($row['status'], ['belum_dilaporkan', 'draft', 'revision', 'rejected'])) {
            if ($row['id_laporan'] && $row['status'] !== 'belum_dilaporkan') {
                $url = route('administrator.monev.laporan.edit') . '?desa_id=' . $row['desa_id'] . '&kegiatan_id=' . $row['kegiatan_id'] . '&id_laporan=' . $row['id_laporan'];
                $icon = 'fas fa-edit';
                $text = 'Edit Laporan';
            } else {
                $url = route('administrator.monev.laporan.create') . '?desa_id=' . $row['desa_id'] . '&kegiatan_id=' . $row['kegiatan_id'];
                $icon = 'fas fa-plus';
                $text = 'Buat Laporan';
            }

            return '<a class="dropdown-item" href="' . $url . '">
                        <i class="' . $icon . ' me-2"></i>' . $text . '
                    </a>';
        }

        return '<a class="dropdown-item disabled" href="javascript:void(0);">
                    <i class="fas fa-check me-2"></i>Sudah Dilaporkan
                </a>';
    }

    /**
     * Format status badge
     */
    private function formatStatusBadge($row)
    {
        $badgeClass = [
            'success' => 'light badge-success',
            'primary' => 'light badge-primary',
            'warning' => 'light badge-warning',
            'danger' => 'light badge-danger',
            'secondary' => 'light badge-secondary',
            'light' => 'light badge-dark'
        ][$row['status_class']] ?? 'light badge';

        return '<span class="badge ' . $badgeClass . '">' . $row['status_display'] . '</span>';
    }

    /**
     * Format bulan
     */
    private function formatBulan($bulan)
    {
        $bulanMap = [
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
        return $bulanMap[$bulan] ?? $bulan;
    }

    /**
     * Format nama kegiatan
     */
    private function formatNamaKegiatan($namaKegiatan)
    {
        if (strlen($namaKegiatan) > 40) {
            return '<span data-bs-toggle="tooltip" title="' . e($namaKegiatan) . '">'
                . substr($namaKegiatan, 0, 40) . '...</span>';
        }
        return $namaKegiatan;
    }

    /**
     * Format nama desa
     */
    private function formatNamaDesa($row)
    {
        return $row['nama_desa'] . ' <br><small class="text-muted">' . $row['nama_kecamatan'] . '</small>';
    }

    /**
     * Format timeline
     */
    private function formatTimeline($row)
    {
        $badgeClass = [
            'Terlambat' => 'light badge-danger',
            'Tenggang' => 'light badge-warning',
            'Menunggu' => 'light badge-success',
            'Tepat Waktu' => 'light badge-primary'
        ][$row['timeline_status']] ?? 'light badge';

        $timelineInfo = '';
        if (!empty($row['tanggal_target'])) {
            $tanggalTarget = \Carbon\Carbon::parse($row['tanggal_target'])->format('d M Y');
            $timelineInfo = '<small class="text-muted d-block" style="font-size: 0.7rem;">Target: ' . $tanggalTarget . '</small>';
        }

        return '<div class="text-center">
                  <span class="badge ' . $badgeClass . ' mb-1">' . $row['timeline_status'] . '</span>
                  <br>' . $timelineInfo . '
                </div>';
    }
}
