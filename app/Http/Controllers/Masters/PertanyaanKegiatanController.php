<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\PertanyaanKegiatan;
use App\Models\Kegiatan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PertanyaanKegiatanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * PertanyaanKegiatanController constructor.
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
     * Display the index view for PertanyaanKegiatan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Pertanyaan Kegiatan');
        return view('administration.masters.pertanyaanKegiatan.index');
    }

    /**
     * Retrieve and return the list of PertanyaanKegiatan for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_kegiatan' => $request->input('filter_kegiatan', ''),
            'search' => $request->input('search', ''),
        ];

        $query = PertanyaanKegiatan::getFilters($filters);

        $this->logActivityService->log('Fetched Pertanyaan Kegiatan list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_pertanyaan . '" name="pertanyaan_ids[]" value="' . $row->id_pertanyaan . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cogs"></i>  Aksi
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_pertanyaan . '">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_pertanyaan . '">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                          
                        </div>
                    </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-success' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->addColumn('kegiatan_info', function ($item) {
                if ($item->kode_kegiatan || $item->nama_kegiatan) {
                    return '<div>
                    <span class="fw-bold">' . $item->kode_kegiatan . '</span><br>
                    <small class="text-muted">' . \Illuminate\Support\Str::limit($item->nama_kegiatan, 40) . '</small>
                </div>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('pertanyaan', function ($item) {
                return \Illuminate\Support\Str::limit($item->pertanyaan, 100);
            })
            ->editColumn('urutan', function ($item) {
                return '<span class="badge badge-light">' . $item->urutan . '</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'kegiatan_info', 'pertanyaan', 'urutan'])
            ->make(true);
    }


    /**
     * Store a new PertanyaanKegiatan record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan',
            'urutan' => 'nullable|integer|min:1|unique:pertanyaan_kegiatan,urutan,NULL,id_pertanyaan,kegiatan_id,' . $request->input('kegiatan_id'),
            'pertanyaan' => 'required|string|max:1000',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Pertanyaan Kegiatan store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the PertanyaanKegiatan
        $result = $this->transactionService->store($request, new PertanyaanKegiatan(), $validationRules);

        $this->logActivityService->log('Stored new Pertanyaan Kegiatan', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific PertanyaanKegiatan by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $pertanyaan = PertanyaanKegiatan::getRelationship($id);

        if (!$pertanyaan) {
            $this->logActivityService->log('Pertanyaan Kegiatan not found for show', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $this->logActivityService->log('Viewed Pertanyaan Kegiatan detail', 'ID: ' . $id);
        return $this->responseService->success($pertanyaan, 'Data retrieved successfully');
    }

    /**
     * Update an existing PertanyaanKegiatan record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $pertanyaan = PertanyaanKegiatan::find($id);

        if (!$pertanyaan) {
            $this->logActivityService->log('Pertanyaan Kegiatan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'kegiatan_id' => 'required|exists:kegiatan,id_kegiatan',
            'urutan' => 'nullable|integer|min:1|unique:pertanyaan_kegiatan,urutan,' . $id . ',id_pertanyaan,kegiatan_id,' . $request->input('kegiatan_id'),
            'pertanyaan' => 'required|string|max:1000',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Pertanyaan Kegiatan update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $pertanyaan, $validationRules);

        $this->logActivityService->log('Updated Pertanyaan Kegiatan', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing PertanyaanKegiatan record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:pertanyaan_kegiatan,id_pertanyaan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Pertanyaan Kegiatan', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the PertanyaanKegiatan records by IDs
        $pertanyaanRecords = PertanyaanKegiatan::whereIn('id_pertanyaan', $selectedIds)->get();

        if ($pertanyaanRecords->isEmpty()) {
            $this->logActivityService->log('No Pertanyaan Kegiatan records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($pertanyaanRecords as $pertanyaan) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $pertanyaan, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Pertanyaan Kegiatan', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Reorder pertanyaan (move up/down).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request, $id)
    {
        $pertanyaan = PertanyaanKegiatan::find($id);

        if (!$pertanyaan) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $direction = $request->input('direction', 'down');

        if ($direction === 'up') {
            // Move up - swap with previous item
            $previous = PertanyaanKegiatan::where('kegiatan_id', $pertanyaan->kegiatan_id)
                ->where('urutan', '<', $pertanyaan->urutan)
                ->orderBy('urutan', 'DESC')
                ->first();

            if ($previous) {
                $tempUrutan = $pertanyaan->urutan;
                $pertanyaan->urutan = $previous->urutan;
                $previous->urutan = $tempUrutan;

                $pertanyaan->save();
                $previous->save();
            }
        } else {
            // Move down - swap with next item
            $next = PertanyaanKegiatan::where('kegiatan_id', $pertanyaan->kegiatan_id)
                ->where('urutan', '>', $pertanyaan->urutan)
                ->orderBy('urutan', 'ASC')
                ->first();

            if ($next) {
                $tempUrutan = $pertanyaan->urutan;
                $pertanyaan->urutan = $next->urutan;
                $next->urutan = $tempUrutan;

                $pertanyaan->save();
                $next->save();
            }
        }

        $this->logActivityService->log('Reordered Pertanyaan Kegiatan', 'ID: ' . $id . ' Direction: ' . $direction);

        return $this->responseService->success($pertanyaan, 'Pertanyaan berhasil diurutkan');
    }

    /**
     * Get pertanyaan by kegiatan ID for dropdown/form.
     *
     * @param int $kegiatanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByKegiatan($kegiatanId)
    {
        $pertanyaan = PertanyaanKegiatan::getByKegiatan($kegiatanId);

        $this->logActivityService->log('Fetched Pertanyaan by Kegiatan', 'Kegiatan ID: ' . $kegiatanId);
        return $this->responseService->success($pertanyaan, 'Data retrieved successfully');
    }
}
