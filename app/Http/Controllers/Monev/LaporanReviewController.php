<?php

namespace App\Http\Controllers\Monev;

use App\Http\Controllers\Controller;
use App\Models\DokumenPersyaratan;
use App\Models\JawabanPertanyaan;
use App\Models\LaporanKegiatan;
use App\Services\FileUploadService;
use App\Services\LaporanReviewService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LaporanReviewController extends Controller
{
    protected $responseService;

    protected $transactionService;

    protected $logActivityService;

    protected $fileUploadService;

    protected $laporanReviewService;

    /**
     * LaporanReviewController constructor.
     */
    public function __construct(
        ResponseService $responseService,
        TransactionService $transactionService,
        LogActivityService $logActivityService,
        FileUploadService $fileUploadService,
        LaporanReviewService $laporanReviewService
    ) {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->fileUploadService = $fileUploadService;
        $this->laporanReviewService = $laporanReviewService;
    }

    /**
     * Display the index view for Laporan Review.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Laporan Review');

        return view('administration.monev.reviewLaporan.index');
    }

   /**
 * Retrieve and return the list of Laporan Kegiatan for DataTables (Inspektorat Review)
 */
public function list(Request $request)
{
    $user = Auth::user();

    if (! $user) {
        abort(401, 'Unauthorized');
    }

    $filters = [
        'filter_status'  => $request->input('filter_status', ''),
        'filter_tahun'   => $request->input('filter_tahun', ''),
        'filter_periode' => $request->input('filter_periode', ''),
        'filter_desa'    => $request->input('filter_desa', ''),
        'search'         => $request->input('search', ''),
    ];

    $data = LaporanKegiatan::getListForReview($filters);

    $this->logActivityService->log('Fetched Laporan Kegiatan list for Review', [
        'user_id' => $user->id_user,
        'filters' => $filters,
    ]);

    return DataTables::of($data)
        ->addColumn('aksi', function ($row) {

            $btnReview = '';
            if ($row['status'] === 'submitted') {
                $btnReview = '<a class="dropdown-item" 
                                href="'.route('administrator.monev.review.review', [
                                    'id_laporan'  => $row['id_laporan'],
                                    'kegiatan_id' => $row['kegiatan_id'],
                                ]).'">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Review Laporan
                              </a>';
            }

            $dropdownItems = $btnReview;

            // Jika sama sekali tidak ada aksi
            if (empty(trim(strip_tags($dropdownItems)))) {
                $dropdownItems .= '<div class="dropdown-item text-muted">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada aksi
                                   </div>';
            }

            return '<div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cogs"></i> Aksi
                        </button>
                        <div class="dropdown-menu">
                            '.$dropdownItems.'
                        </div>
                    </div>';
        })

        ->editColumn('status_display', function ($row) {
            $badgeClass = [
                'success'   => 'light badge-success',
                'primary'   => 'light badge-primary',
                'warning'   => 'light badge-warning',
                'danger'    => 'light badge-danger',
                'secondary' => 'light badge-secondary',
                'light'     => 'light badge-dark',
            ][$row['status_class']] ?? 'light badge';

            return '<span class="badge '.$badgeClass.'">'.$row['status_display'].'</span>';
        })

        ->editColumn('bulan', function ($row) {
            $bulan = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
            ];

            return $bulan[$row['bulan']] ?? $row['bulan'];
        })

        ->editColumn('nama_kegiatan', function ($row) {
            if (strlen($row['nama_kegiatan']) > 40) {
                return '<span data-bs-toggle="tooltip" 
                             title="'.e($row['nama_kegiatan']).'">'
                        .substr($row['nama_kegiatan'], 0, 40).'...</span>';
            }
            return $row['nama_kegiatan'];
        })

        ->editColumn('nama_desa', function ($row) {
            return $row['nama_desa'].' <br><small class="text-muted">'.$row['nama_kecamatan'].'</small>';
        })

        ->addColumn('timeline', function ($row) {
            $badgeClass = [
                'Terlambat'    => 'light badge-danger',
                'Tenggang'     => 'light badge-warning',
                'Menunggu'     => 'light badge-success',
                'Tepat Waktu'  => 'light badge-primary',
            ][$row['timeline_status']] ?? 'light badge';

            $timelineInfo = '';

            if (! empty($row['tanggal_target'])) {
                $timelineInfo .= '<small class="text-muted d-block" style="font-size: 0.7rem;">Target: '
                               . \Carbon\Carbon::parse($row['tanggal_target'])->format('d M Y')
                               . '</small>';
            }

            if (! empty($row['tanggal_submit'])) {
                $timelineInfo .= '<small class="text-muted d-block" style="font-size: 0.7rem;">Submit: '
                               . \Carbon\Carbon::parse($row['tanggal_submit'])->format('d M Y')
                               . '</small>';
            }

            return '<div class="text-center">
                        <span class="badge '.$badgeClass.' mb-1">'.$row['timeline_status'].'</span>
                        <br>'.$timelineInfo.'
                    </div>';
        })

        ->addColumn('review_info', function ($row) {
            if ($row['status'] === 'submitted') {
                return '<span class="badge badge-primary">Butuh Review</span>';
            }
            if ($row['status'] === 'revision') {
                return '<span class="badge badge-warning">Menunggu Revisi</span>';
            }
            return '';
        })

        ->rawColumns(['aksi', 'status_display', 'timeline', 'nama_kegiatan', 'nama_desa', 'review_info'])
        ->make(true);
}

    /**
     * Display the review view for existing Laporan Review.
     */
    public function review(Request $request)
    {
        $this->logActivityService->log('Accessed review view for Laporan Review');

        $desa_id = $request->query('desa_id');
        $kegiatan_id = $request->query('kegiatan_id');
        $id_laporan = $request->query('id_laporan');

        return view('administration.monev.reviewLaporan.review', compact('desa_id', 'kegiatan_id', 'id_laporan'));
    }

    public function submit(Request $request)
    {
        $validator = $this->validateReviewRequest($request);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Laporan Review submission');

            return $this->responseService->validationError($validator->errors());
        }

        // Validasi tambahan: Untuk dokumen dengan status revision, wajib ada catatan
        $validationErrors = $this->validateDokumenRevisionNotes($request);
        if (! empty($validationErrors)) {
            return $this->responseService->validationError($validationErrors);
        }

        try {
            $validatedData = $validator->validated();

            $this->laporanReviewService->submitReview(
                $validatedData['laporan_id'],
                $validatedData,
                $request->dokumen_status ?? [],
                $request->catatan_revisi ?? []
            );

            $this->logActivityService->log('Submitted Laporan Review', [
                'laporan_id' => $validatedData['laporan_id'],
                'status' => $validatedData['status'],
                'reviewer_id' => Auth::id(),
            ]);

            return $this->responseService->success(
                null,
                'Review berhasil disimpan'
            );
        } catch (\Exception $e) {
            Log::error('Error submitting laporan review: '.$e->getMessage());

            return $this->responseService->error('Gagal menyimpan review: '.$e->getMessage());
        }
    }

    /**
     * Validation rules for review request
     */
    private function validateReviewRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'laporan_id' => 'required|exists:laporan_kegiatan,id_laporan',
            'status' => 'required|in:revision,approved',
            'catatan_approval' => 'nullable|string|max:1000',
            'dokumen_status' => 'sometimes|array',
            'dokumen_status.*' => 'sometimes|array',
            'dokumen_status.*.*' => 'sometimes|in:approved,revision',
            'catatan_revisi' => 'sometimes|array',
            'catatan_revisi.*' => 'sometimes|array',
            'catatan_revisi.*.*' => 'nullable|string|max:500',
        ], [
            'laporan_id.required' => 'ID Laporan wajib diisi',
            'laporan_id.exists' => 'Laporan tidak ditemukan',
            'status.required' => 'Status review wajib diisi',
            'status.in' => 'Status harus revision atau approved',
            'catatan_approval.max' => 'Catatan approval maksimal 1000 karakter',
            'dokumen_status.*.*.in' => 'Status dokumen harus approved atau revision',
            'catatan_revisi.*.*.max' => 'Catatan revisi maksimal 500 karakter',
        ]);
    }

    /**
     * Validasi tambahan: Untuk dokumen dengan status revision, wajib ada catatan
     */
    private function validateDokumenRevisionNotes(Request $request)
    {
        $errors = [];
        $dokumenStatus = $request->dokumen_status ?? [];
        $catatanRevisi = $request->catatan_revisi ?? [];

        foreach ($dokumenStatus as $questionId => $requirements) {
            foreach ($requirements as $requirementId => $status) {
                if ($status === 'revision') {
                    $catatan = $catatanRevisi[$questionId][$requirementId] ?? null;
                    if (empty($catatan) || trim($catatan) === '') {
                        $errors["catatan_revisi.{$questionId}.{$requirementId}"] = [
                            'Catatan revisi wajib diisi untuk dokumen yang memerlukan revisi',
                        ];
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Display the review form for Laporan Kegiatan
     */
    public function showReviewForm(Request $request)
    {
        $this->logActivityService->log('Accessed review form for Laporan Kegiatan');

        $id_laporan = $request->query('id_laporan');

        return view('administration.monev.reviewLaporan.review', compact('id_laporan'));
    }

    /**
     * Get laporan data for review
     */
    public function getData($id)
    {
        try {
            // Get existing laporan data for review
            $laporan = LaporanKegiatan::getWithRelationships($id);

            if (! $laporan) {
                return $this->responseService->error('Data laporan tidak ditemukan');
            }

            // Only get current dokumen for review
            $data = [
                'laporan' => $laporan,
                'jawaban' => JawabanPertanyaan::where('laporan_id', $id)->get(),
                'dokumen' => DokumenPersyaratan::whereIn(
                    'jawaban_id',
                    JawabanPertanyaan::where('laporan_id', $id)->pluck('id_jawaban')
                )->where('is_current', true)->get(),
            ];

            Log::info("Review data sent to frontend for laporan $id", [
                'total_dokumen' => $data['dokumen']->count(),
                'status' => $laporan->status,
            ]);

            return $this->responseService->success($data);
        } catch (\Exception $e) {
            Log::error('Error getting review data: '.$e->getMessage());

            return $this->responseService->error('Terjadi kesalahan saat mengambil data review');
        }
    }
}
