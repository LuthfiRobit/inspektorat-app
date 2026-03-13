<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Persyaratan;
use App\Models\PertanyaanKegiatan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PersyaratanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * PersyaratanController constructor.
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
     * Display the index view for Persyaratan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Persyaratan');
        return view('administration.masters.persyaratan.index');
    }

    /**
     * Retrieve and return the list of Persyaratan for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tipe' => $request->input('filter_tipe', ''),
            'filter_pertanyaan' => $request->input('filter_pertanyaan', ''),
            'search' => $request->input('search', ''),
        ];

        $query = Persyaratan::getFilters($filters);
        // return $query;

        $this->logActivityService->log('Fetched Persyaratan list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_persyaratan . '" name="persyaratan_ids[]" value="' . $row->id_persyaratan . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cogs"></i>  Aksi
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_persyaratan . '">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_persyaratan . '">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-success' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->editColumn('tipe', function ($item) {
                $badgeClass = ($item->tipe == 'wajib') ? 'light badge-primary' : 'light badge-secondary';
                $tipeText = ($item->tipe == 'wajib') ? 'WAJIB' : 'TAMBAHAN';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . $tipeText . '</span>';
            })
            ->addColumn('pertanyaan_info', function ($item) {
                // Karena hasilnya query builder, akses langsung field yg di-select
                if ($item->pertanyaan) {
                    return '<div>
                    <span class="fw-bold">' . $item->kode_kegiatan . ' - ' . \Illuminate\Support\Str::limit($item->nama_kegiatan, 30) . '</span><br>
                    <small class="text-muted">' . \Illuminate\Support\Str::limit($item->pertanyaan, 50) . '</small>
                </div>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('nama_persyaratan', function ($item) {
                return '<div>
                <div class="fw-bold">' . $item->nama_persyaratan . '</div>' .
                    ($item->deskripsi ? '<small class="text-muted">' . \Illuminate\Support\Str::limit($item->deskripsi, 50) . '</small>' : '') .
                    '</div>';
            })
            ->editColumn('urutan', function ($item) {
                return '<span class="badge badge-light">' . $item->urutan . '</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'tipe', 'pertanyaan_info', 'nama_persyaratan', 'urutan'])
            ->make(true);
    }

    /**
     * Store a new Persyaratan record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validation rules
        $validationRules = [
            'pertanyaan_kegiatan_id' => 'required|exists:pertanyaan_kegiatan,id_pertanyaan',
            'urutan' => 'nullable|integer|min:1',
            'nama_persyaratan' => 'required|string|max:200',
            'template_persyaratan' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // max 2MB
            'deskripsi' => 'nullable|string|max:500',
            'tipe' => 'required|in:wajib,tambahan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log(
                'Validation failed during Persyaratan store',
                'Errors: ' . json_encode($validator->errors())
            );
            return $this->responseService->validationError($validator->errors());
        }

        // Handle file upload for 'template_persyaratan'
        $fileFields = [];
        $oldFiles = [];

        if ($request->hasFile('template_persyaratan')) {
            $fileFields = [
                'template_persyaratan' => 'persyaratan/template', // Folder tujuan
            ];
        }

        // Log sebelum penyimpanan
        $this->logActivityService->log('Stored new Persyaratan', 'Data: ' . json_encode($request->all()));

        // Simpan data dengan TransactionService
        $result = $this->transactionService->store(
            $request,
            new Persyaratan(),
            $validationRules,
            null,           // Optional closure logic, not needed here
            $fileFields,    // Files to upload
            $oldFiles       // Files to delete (optional, kosong sekarang)
        );

        return $result;
    }


    /**
     * Display the details of a specific Persyaratan by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $persyaratan = Persyaratan::getRelationship($id);

        if (!$persyaratan) {
            $this->logActivityService->log('Persyaratan not found for show', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $this->logActivityService->log('Viewed Persyaratan detail', 'ID: ' . $id);
        return $this->responseService->success($persyaratan, 'Data retrieved successfully');
    }

    /**
     * Update an existing Persyaratan record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $persyaratan = Persyaratan::find($id);

        if (!$persyaratan) {
            $this->logActivityService->log('Persyaratan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'pertanyaan_kegiatan_id' => 'required|exists:pertanyaan_kegiatan,id_pertanyaan',
            'urutan' => 'nullable|integer|min:1',
            'nama_persyaratan' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:500',
            'tipe' => 'required|in:wajib,tambahan',
            'status' => 'required|in:active,inactive',
            'template_persyaratan' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // max 2MB
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Persyaratan update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Siapkan fileFields dan oldFiles
        $fileFields = [];
        $oldFiles = [];

        if ($request->hasFile('template_persyaratan')) {
            $fileFields = [
                'template_persyaratan' => 'persyaratan/template',
            ];

            if (!empty($persyaratan->template_persyaratan)) {
                $oldFiles = [
                    'template_persyaratan' => $persyaratan->template_persyaratan,
                ];
            }
        }

        // Gunakan TransactionService untuk update dengan file handling
        $result = $this->transactionService->update(
            $request,
            $persyaratan,
            $validationRules,
            null,        // tidak ada custom logic tambahan
            $fileFields, // file fields yang akan diupload
            $oldFiles    // file lama yang akan dihapus jika ada upload baru
        );

        $this->logActivityService->log('Updated Persyaratan', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing Persyaratan record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:persyaratan,id_persyaratan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Persyaratan', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the Persyaratan records by IDs
        $persyaratanRecords = Persyaratan::whereIn('id_persyaratan', $selectedIds)->get();

        if ($persyaratanRecords->isEmpty()) {
            $this->logActivityService->log('No Persyaratan records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($persyaratanRecords as $persyaratan) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $persyaratan, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Persyaratan', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Reorder persyaratan (move up/down).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request, $id)
    {
        $persyaratan = Persyaratan::find($id);

        if (!$persyaratan) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $direction = $request->input('direction', 'down');

        if ($direction === 'up') {
            // Move up - swap with previous item
            $previous = Persyaratan::where('pertanyaan_kegiatan_id', $persyaratan->pertanyaan_kegiatan_id)
                ->where('urutan', '<', $persyaratan->urutan)
                ->orderBy('urutan', 'DESC')
                ->first();

            if ($previous) {
                $tempUrutan = $persyaratan->urutan;
                $persyaratan->urutan = $previous->urutan;
                $previous->urutan = $tempUrutan;

                $persyaratan->save();
                $previous->save();
            }
        } else {
            // Move down - swap with next item
            $next = Persyaratan::where('pertanyaan_kegiatan_id', $persyaratan->pertanyaan_kegiatan_id)
                ->where('urutan', '>', $persyaratan->urutan)
                ->orderBy('urutan', 'ASC')
                ->first();

            if ($next) {
                $tempUrutan = $persyaratan->urutan;
                $persyaratan->urutan = $next->urutan;
                $next->urutan = $tempUrutan;

                $persyaratan->save();
                $next->save();
            }
        }

        $this->logActivityService->log('Reordered Persyaratan', 'ID: ' . $id . ' Direction: ' . $direction);

        return $this->responseService->success($persyaratan, 'Persyaratan berhasil diurutkan');
    }

    /**
     * Get persyaratan by pertanyaan kegiatan ID for dropdown/form.
     *
     * @param int $pertanyaanKegiatanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPertanyaanKegiatan($pertanyaanKegiatanId)
    {
        $persyaratan = Persyaratan::where('pertanyaan_kegiatan_id', $pertanyaanKegiatanId)
            ->where('status', 'active')
            ->orderBy('urutan', 'ASC')
            ->get(['id_persyaratan', 'urutan', 'nama_persyaratan', 'deskripsi', 'tipe']);

        $this->logActivityService->log('Fetched Persyaratan by Pertanyaan Kegiatan', 'Pertanyaan Kegiatan ID: ' . $pertanyaanKegiatanId);
        return $this->responseService->success($persyaratan, 'Data retrieved successfully');
    }
}
