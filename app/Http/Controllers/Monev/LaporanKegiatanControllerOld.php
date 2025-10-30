<?php

namespace App\Http\Controllers\Monev;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\DokumenPersyaratan;
use App\Models\HistoryDokumen;
use App\Models\HistoryLaporan;
use App\Models\JawabanPertanyaan;
use App\Models\Kegiatan;
use App\Models\LaporanKegiatan;
use App\Models\PertanyaanKegiatan;
use App\Services\FileUploadService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LaporanKegiatanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $fileUploadService;

    /**
     * LaporanKegiatanController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService,  LogActivityService $logActivityService,  FileUploadService $fileUploadService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display the index view for Laporan Kegiatan.
     *
     * @return \Illuminate\View\View
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
            'filter_desa' => $request->input('filter_desa', ''),
            'search' => $request->input('search', ''),
        ];

        $data = LaporanKegiatan::getListForUser($user, $filters);

        $this->logActivityService->log('Fetched Laporan Kegiatan list for User', [
            'user_id' => $user->id_user,
            'filters' => $filters
        ]);
        // return $data;
        return DataTables::of($data)
            ->addColumn('checkbox', function ($row) {
                $isDisabled = in_array($row['status'], ['submitted', 'approved', 'rejected', 'belum_dilaporkan']) ? 'disabled' : '';
                $value = $row['status'] === 'belum_dilaporkan' ? '' : ($row['id_laporan'] ?? '');
                return '<input type="checkbox" class="table-checkbox form-check-input" ' . $isDisabled .
                    ' id="checkbox_' . ($row['id_laporan'] ?? 'new') . '" name="laporan_ids[]" value="' . $value . '">';
            })
            ->addColumn('aksi', function ($row) {
                $btnDetail = '<a class="dropdown-item" href="javascript:void(0);" data-action="action_show" 
                                    data-kegiatan-id="' . $row['kegiatan_id'] . '" 
                                    data-desa-id="' . $row['desa_id'] . '" 
                                    data-tahun="' . $row['tahun'] . '" 
                                    data-bulan="' . $row['bulan'] . '"
                                    data-id="' . $row['kegiatan_id'] . '">
                                <i class="fas fa-eye me-2"></i>Detail
                            </a>';

                $btnLaporkan = '';
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

                    $btnLaporkan = '<a class="dropdown-item" href="' . $url . '">
                                        <i class="' . $icon . ' me-2"></i>' . $text . '
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
                    'light' => 'light badge-dark'
                ][$row['status_class']] ?? 'light badge';

                return '<span class="badge ' . $badgeClass . '">' . $row['status_display'] . '</span>';
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
                return $row['nama_desa'] . ' <br><small class="text-muted">' . $row['nama_kecamatan'] . '</small>';
            })
            ->addColumn('timeline', function ($row) {
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

    /**
     * Display the create view for Laporan Kegiatan.
     *
     * @return \Illuminate\View\View
     */
    // LaporanKegiatanController.php

    public function create(Request $request)
    {
        $this->logActivityService->log('Accessed create view for Laporan Kegiatan');

        $desa_id = $request->query('desa_id');
        $kegiatan_id = $request->query('kegiatan_id');
        $id_laporan = $request->query('id_laporan');

        return view('administration.monev.buatLaporan.create-new', compact('desa_id', 'kegiatan_id', 'id_laporan'));
    }

    public function getData($id)
    {
        // Get existing laporan data (for draft/revision)
        $laporan = LaporanKegiatan::getWithRelationships($id);
        if (!$laporan) {
            return $this->responseService->error('Data tidak ditemukan');
        }

        // **PASTIKAN: Hanya ambil dokumen dengan is_current = true**
        $data = [
            'laporan' => $laporan,
            'jawaban' => JawabanPertanyaan::where('laporan_id', $id)->get(),
            'dokumen' => DokumenPersyaratan::whereIn(
                'jawaban_id',
                JawabanPertanyaan::where('laporan_id', $id)->pluck('id_jawaban')
            )->where('is_current', true) // **FILTER HANYA CURRENT**
                ->get()
        ];

        // **DEBUG: Log untuk memastikan**
        Log::info("Dokumen data sent to frontend for laporan $id", [
            'total_dokumen' => $data['dokumen']->count(),
            'dokumen_list' => $data['dokumen']->pluck('id_dokumen', 'persyaratan_id')
        ]);

        return $this->responseService->success($data);
    }

    /**
     * Get kegiatan data with questions and requirements
     */
    public function getKegiatanData(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'desa_id' => 'required|exists:desa,id_desa',
                'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan'
            ]);

            if ($validator->fails()) {
                return $this->responseService->validationError($validator->errors());
            }

            $desa = Desa::getRelationship($request->desa_id);
            // Get kegiatan data
            $kegiatan = Kegiatan::getRelationship($request->kegiatan_id);

            if (!$kegiatan) {
                return $this->responseService->error('Data kegiatan tidak ditemukan', 404);
            }

            // Get pertanyaan dengan persyaratan
            $pertanyaan = PertanyaanKegiatan::with(['persyaratan' => function ($query) {
                $query->where('status', 'active')->orderBy('urutan');
            }])
                ->where('kegiatan_id', $request->kegiatan_id)
                ->where('status', 'active')
                ->orderBy('urutan')
                ->get();

            $data = [
                'desa' => $desa,
                'kegiatan' => $kegiatan,
                'pertanyaan' => $pertanyaan
            ];

            return $this->responseService->success($data, 'Data berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Error in getKegiatanData: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan server: ' . $e->getMessage());
        }
    }

    /**
     * Display the create view for new Laporan Kegiatan.
     */
    public function createNew(Request $request)
    {
        $this->logActivityService->log('Accessed create new view for Laporan Kegiatan');

        $desa_id = $request->query('desa_id');
        $kegiatan_id = $request->query('kegiatan_id');

        return view('administration.monev.buatLaporan.create-new', compact('desa_id', 'kegiatan_id'));
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
     * Store a new Laporan Kegiatan.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'desa_id' => 'required|exists:desa,id_desa',
            'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'status' => 'required|in:draft,submitted',
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|in:sudah,belum',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log(
                'Validation failed during Laporan Kegiatan store',
                'Errors: ' . json_encode($validator->errors())
            );
            return $this->responseService->validationError($validator->errors());
        }

        DB::beginTransaction();
        try {
            // Create laporan kegiatan
            $laporan = new LaporanKegiatan();
            $laporan->desa_id = $request->desa_id;
            $laporan->kegiatan_id = $request->kegiatan_id;
            $laporan->tahun = $request->tahun;
            $laporan->bulan = $request->bulan;
            $laporan->status = $request->status;
            $laporan->created_by = Auth::id();

            // Calculate target date based on kegiatan
            $kegiatan = Kegiatan::find($request->kegiatan_id);
            if ($kegiatan) {
                $laporan->tanggal_target = $this->calculateTargetDate($kegiatan);
            }

            if ($request->status == 'submitted') {
                $laporan->tanggal_submit = now();
            }

            $laporan->save();

            // Save jawaban
            foreach ($request->jawaban as $pertanyaanId => $jawabanValue) {
                $jawaban = new JawabanPertanyaan();
                $jawaban->laporan_id = $laporan->id_laporan;
                $jawaban->pertanyaan_id = $pertanyaanId;
                $jawaban->jawaban_text = $jawabanValue;
                $jawaban->status = $request->status;
                $jawaban->save();

                Log::info('Jawaban saved', [
                    'laporan_id' => $laporan->id_laporan,
                    'pertanyaan_id' => $pertanyaanId,
                    'jawaban_text' => $jawabanValue,
                    'jawaban_id' => $jawaban->id_jawaban
                ]);

                // Process files menggunakan FileUploadService
                $this->processFilesWithService($request, $laporan->id_laporan, $jawaban->id_jawaban, $pertanyaanId, $request->status);
            }

            DB::commit();

            $this->logActivityService->log('Stored new Laporan Kegiatan', 'Laporan ID: ' . $laporan->id_laporan);

            return $this->responseService->success(
                ['laporan_id' => $laporan->id_laporan],
                'Laporan berhasil disimpan'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing laporan: ' . $e->getMessage());
            return $this->responseService->error('Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Process files using FileUploadService
     */
    private function processFilesWithService($request, $laporanId, $jawabanId, $pertanyaanId, $laporanStatus)
    {
        try {
            Log::info("Processing files for question: $pertanyaanId, jawaban: $jawabanId");

            $allFiles = $request->allFiles();

            if (empty($allFiles) || !isset($allFiles['files']) || !isset($allFiles['files'][$pertanyaanId])) {
                Log::info("No files found for question: $pertanyaanId");
                return;
            }

            $questionFiles = $allFiles['files'][$pertanyaanId];

            foreach ($questionFiles as $persyaratanId => $file) {
                if ($file && $file->isValid()) {
                    $this->saveSingleFileWithService($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $laporanStatus);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in processFilesWithService: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save single file using FileUploadService dengan method baru
     */
    private function saveSingleFileWithService($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $laporanStatus)
    {
        try {
            $originalName = $file->getClientOriginalName();

            // Generate custom filename
            $customFileName = 'dokumen_' . $laporanId . '_' . $pertanyaanId . '_' . $persyaratanId . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Gunakan method uploadSingleFile yang baru
            $filePath = $this->fileUploadService->uploadSingleFile(
                $file,
                'dokumen/laporan',
                ['mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'], // bisa array atau string
                $customFileName
            );

            Log::info('File stored successfully with FileUploadService:', [
                'original_name' => $originalName,
                'stored_path' => $filePath,
                'jawaban_id' => $jawabanId,
                'persyaratan_id' => $persyaratanId
            ]);

            $dokumen = new DokumenPersyaratan();
            $dokumen->jawaban_id = $jawabanId;
            $dokumen->persyaratan_id = $persyaratanId;
            $dokumen->nama_file = $originalName;
            $dokumen->path_file = $filePath;
            $dokumen->status = $laporanStatus;
            $dokumen->created_by = Auth::id();
            $dokumen->save();

            Log::info('Dokumen saved to database:', ['dokumen_id' => $dokumen->id_dokumen]);
        } catch (\Exception $e) {
            Log::error('Error saving single file with service: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate target date based on kegiatan data
     */
    private function calculateTargetDate($kegiatan)
    {
        $bulan = $kegiatan->bulan;
        $tahun = $kegiatan->tahun;
        $batasAkhir = $kegiatan->batas_akhir_upload ?? 15;

        $tanggalTarget = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->addDays($batasAkhir);

        return $tanggalTarget;
    }

    /**
     * Update an existing Laporan Kegiatan.
     */
    public function update(Request $request, $id)
    {
        $laporan = LaporanKegiatan::where('id_laporan', $id)->first();

        if (!$laporan) {
            $this->logActivityService->log('Laporan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data tidak ditemukan', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'desa_id' => 'required|exists:desa,id_desa',
            'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'status' => 'required|in:draft,submitted',
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|in:sudah,belum',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log(
                'Validation failed during Laporan Kegiatan update',
                'Errors: ' . json_encode($validator->errors())
            );
            return $this->responseService->validationError($validator->errors());
        }

        DB::beginTransaction();
        try {
            $oldStatus = $laporan->status;

            // Update laporan
            $laporan->desa_id = $request->desa_id;
            $laporan->kegiatan_id = $request->kegiatan_id;
            $laporan->tahun = $request->tahun;
            $laporan->bulan = $request->bulan;
            $laporan->status = $request->status;

            if ($request->status == 'submitted' && $oldStatus != 'submitted') {
                $laporan->tanggal_submit = now();
            }

            $laporan->save();

            // Record history laporan JIKA status berubah
            if ($oldStatus != $request->status) {
                $this->createLaporanHistory($laporan->id_laporan, $oldStatus, $request->status, $laporan->catatan_approval);
            }

            // Update atau buat jawaban
            foreach ($request->jawaban as $pertanyaanId => $jawabanValue) {
                $jawaban = JawabanPertanyaan::where('laporan_id', $laporan->id_laporan)
                    ->where('pertanyaan_id', $pertanyaanId)
                    ->first();

                if (!$jawaban) {
                    $jawaban = new JawabanPertanyaan();
                    $jawaban->laporan_id = $laporan->id_laporan;
                    $jawaban->pertanyaan_id = $pertanyaanId;
                }

                $jawaban->jawaban_text = $jawabanValue;
                $jawaban->status = $request->status;
                $jawaban->save();

                // Process files untuk pertanyaan ini
                $this->processFilesForUpdate($request, $laporan->id_laporan, $jawaban->id_jawaban, $pertanyaanId, $request->status);
            }

            // **PERBAIKAN PENTING: Update status SEMUA dokumen current yang belum diubah**
            if ($oldStatus != $request->status) {
                $this->updateAllCurrentDokumenStatus($laporan->id_laporan, $request->status);
            }

            DB::commit();

            $this->logActivityService->log('Updated Laporan Kegiatan', 'Laporan ID: ' . $laporan->id_laporan);

            return $this->responseService->success(
                ['laporan_id' => $laporan->id_laporan],
                'Laporan berhasil diupdate'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating laporan: ' . $e->getMessage());
            return $this->responseService->error('Gagal mengupdate laporan: ' . $e->getMessage());
        }
    }

    /**
     * Process files for update - hanya proses file yang benar-benar ada di request
     */
    private function processFilesForUpdate($request, $laporanId, $jawabanId, $pertanyaanId, $laporanStatus)
    {
        try {
            $allFiles = $request->allFiles();

            Log::info("=== Processing Files for Update ===", [
                'laporan_id' => $laporanId,
                'jawaban_id' => $jawabanId,
                'pertanyaan_id' => $pertanyaanId,
                'has_files' => !empty($allFiles),
                'files_structure' => $allFiles ? array_keys($allFiles) : 'no files'
            ]);

            if (empty($allFiles) || !isset($allFiles['files']) || !isset($allFiles['files'][$pertanyaanId])) {
                Log::info("🚫 No new files for question: $pertanyaanId");
                return;
            }

            $questionFiles = $allFiles['files'][$pertanyaanId];
            Log::info("📁 Files for question $pertanyaanId:", array_keys($questionFiles));

            $processedCount = 0;
            foreach ($questionFiles as $persyaratanId => $file) {
                // HANYA proses jika file valid dan benar-benar ada
                if ($file && $file->isValid() && $file->getSize() > 0) {
                    Log::info("🔄 Processing file update for requirement: $persyaratanId", [
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize()
                    ]);
                    $this->handleFileRevision($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $laporanStatus);
                    $processedCount++;
                } else {
                    Log::info("🚫 Skipping invalid/empty file for requirement: $persyaratanId", [
                        'is_valid' => $file ? $file->isValid() : 'no file',
                        'file_size' => $file ? $file->getSize() : 0
                    ]);
                }
            }

            Log::info("✅ Processed $processedCount files for question: $pertanyaanId");
        } catch (\Exception $e) {
            Log::error('❌ Error in processFilesForUpdate: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle file revision - TANPA HISTORY DOKUMEN
     * Hanya update dokumen_persyaratan dengan versioning
     */
    private function handleFileRevision($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $laporanStatus)
    {
        try {
            $originalName = $file->getClientOriginalName();

            // Cek dokumen existing yang sedang aktif
            $existingDokumen = DokumenPersyaratan::where('jawaban_id', $jawabanId)
                ->where('persyaratan_id', $persyaratanId)
                ->where('is_current', true)
                ->first();

            Log::info("=== File Revision Processing ===", [
                'pertanyaan_id' => $pertanyaanId,
                'persyaratan_id' => $persyaratanId,
                'existing_dokumen' => $existingDokumen ? $existingDokumen->id_dokumen : 'none',
                'new_file_name' => $originalName,
                'existing_file_name' => $existingDokumen ? $existingDokumen->nama_file : 'none'
            ]);

            // **CEK PERUBAHAN FILE: Hanya proses jika file benar-benar berbeda**
            if ($existingDokumen) {
                $isDifferentFile = $this->isFileTrulyChanged($existingDokumen, $file, $originalName);

                if (!$isDifferentFile) {
                    Log::info("🚫 FILE UNCHANGED - Skipping for requirement: $persyaratanId");
                    return; // **KELUAR - tidak proses file yang tidak berubah**
                }

                Log::info("✅ FILE CHANGED - Proceeding with update for requirement: $persyaratanId");

                // **NONAKTIFKAN FILE LAMA: Set is_current = false**
                $existingDokumen->is_current = false;
                $existingDokumen->save();

                Log::info("Disabled previous dokumen: " . $existingDokumen->id_dokumen);
            }

            // **UPLOAD FILE BARU**
            $customFileName = 'dokumen_' . $laporanId . '_' . $pertanyaanId . '_' . $persyaratanId . '_' . time() . '.' . $file->getClientOriginalExtension();

            $filePath = $this->fileUploadService->uploadSingleFile(
                $file,
                'dokumen/laporan',
                ['mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
                $customFileName
            );

            // **HITUNG VERSION BARU: Berdasarkan jumlah dokumen sebelumnya**
            $previousVersionsCount = DokumenPersyaratan::where('jawaban_id', $jawabanId)
                ->where('persyaratan_id', $persyaratanId)
                ->count();

            // **BUAT DOKUMEN BARU: Sebagai current version**
            $dokumen = new DokumenPersyaratan();
            $dokumen->jawaban_id = $jawabanId;
            $dokumen->persyaratan_id = $persyaratanId;
            $dokumen->nama_file = $originalName;
            $dokumen->path_file = $filePath;
            $dokumen->status = $laporanStatus;
            $dokumen->version = $previousVersionsCount + 1;
            $dokumen->is_current = true;
            $dokumen->created_by = Auth::id();
            $dokumen->save();

            Log::info('✅ File update COMPLETED:', [
                'new_dokumen_id' => $dokumen->id_dokumen,
                'previous_dokumen_id' => $existingDokumen ? $existingDokumen->id_dokumen : null,
                'version' => $dokumen->version,
                'total_versions' => $previousVersionsCount + 1
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error handling file revision: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if file is truly changed (berdasarkan nama atau content)
     */
    private function isFileTrulyChanged($existingDokumen, $newFile, $newFileName)
    {
        Log::info("🔍 Checking file changes:", [
            'existing_name' => $existingDokumen->nama_file,
            'new_name' => $newFileName,
            'existing_path' => $existingDokumen->path_file
        ]);

        // Cek 1: Bandingkan nama file
        if ($existingDokumen->nama_file !== $newFileName) {
            Log::info("📝 File name changed: '{$existingDokumen->nama_file}' -> '{$newFileName}'");
            return true;
        } else {
            Log::info("📝 File name UNCHANGED: '{$existingDokumen->nama_file}'");
        }

        // Cek 2: Bandingkan size file
        $existingFileSize = 0;
        try {
            if (Storage::exists($existingDokumen->path_file)) {
                $existingFileSize = Storage::size($existingDokumen->path_file);
                Log::info("💾 Existing file size: " . $existingFileSize);
            } else {
                Log::warning("💾 Existing file not found in storage: " . $existingDokumen->path_file);
            }
        } catch (\Exception $e) {
            Log::warning("💾 Cannot get existing file size: " . $e->getMessage());
        }

        $newFileSize = $newFile->getSize();
        Log::info("💾 New file size: " . $newFileSize);

        if ($existingFileSize !== $newFileSize) {
            Log::info("💾 File size changed: {$existingFileSize} -> {$newFileSize}");
            return true;
        } else {
            Log::info("💾 File size UNCHANGED: {$existingFileSize}");
        }

        // Cek 3: Bandingkan hash file (paling akurat)
        try {
            $existingFileHash = null;
            if (Storage::exists($existingDokumen->path_file)) {
                $existingFileContent = Storage::get($existingDokumen->path_file);
                $existingFileHash = md5($existingFileContent);
                Log::info("🔑 Existing file hash: " . substr($existingFileHash, 0, 10) . "...");
            } else {
                Log::warning("🔑 Existing file not found for hash comparison");
            }

            $newFileHash = md5_file($newFile->getRealPath());
            Log::info("🔑 New file hash: " . substr($newFileHash, 0, 10) + "...");

            if ($existingFileHash !== $newFileHash) {
                Log::info("🔑 File content changed (hash different)");
                return true;
            } else {
                Log::info("🔑 File content UNCHANGED (hash same)");
            }
        } catch (\Exception $e) {
            Log::warning("🔑 Cannot compare file hash: " . $e->getMessage());
            // Jika tidak bisa bandingkan hash, anggap file berubah untuk safety
            return true;
        }

        // Jika semua sama, file tidak berubah
        Log::info("🎯 File is IDENTICAL - no changes detected");
        return false;
    }

    /**
     * Create history record for laporan status change
     */
    private function createLaporanHistory($laporanId, $statusSebelum, $statusSesudah, $catatanApproval = null)
    {
        try {
            $historyLaporan = new HistoryLaporan();
            $historyLaporan->laporan_id = $laporanId;
            $historyLaporan->status_sebelum = $statusSebelum;
            $historyLaporan->status_sesudah = $statusSesudah;
            $historyLaporan->catatan_perubahan = $catatanApproval;
            $historyLaporan->changed_by = Auth::id();
            $historyLaporan->save();

            Log::info('Laporan history created:', [
                'laporan_id' => $laporanId,
                'from' => $statusSebelum,
                'to' => $statusSesudah,
                'catatan' => $catatanApproval
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating laporan history: ' . $e->getMessage());
        }
    }

    /**
     * Update status SEMUA dokumen current yang terkait dengan laporan
     * Digunakan ketika status laporan berubah (draft → submitted atau sebaliknya)
     */
    private function updateAllCurrentDokumenStatus($laporanId, $newStatus)
    {
        try {
            // Dapatkan semua jawaban untuk laporan ini
            $jawabanIds = JawabanPertanyaan::where('laporan_id', $laporanId)
                ->pluck('id_jawaban');

            if ($jawabanIds->isEmpty()) {
                Log::info("No jawaban found for laporan: $laporanId");
                return;
            }

            // Update SEMUA dokumen current yang terkait dengan jawaban-jawaban ini
            $updatedCount = DokumenPersyaratan::whereIn('jawaban_id', $jawabanIds)
                ->where('is_current', true)
                ->update(['status' => $newStatus]);

            Log::info("✅ Updated $updatedCount current dokumen to status: $newStatus for laporan: $laporanId", [
                'laporan_id' => $laporanId,
                'new_status' => $newStatus,
                'updated_count' => $updatedCount,
                'jawaban_ids' => $jawabanIds->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating all current dokumen status: ' . $e->getMessage());
            // Jangan throw error, biarkan proses utama tetap berjalan
        }
    }
}
