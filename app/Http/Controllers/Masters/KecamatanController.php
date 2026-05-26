<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KecamatanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * KecamatanController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService, LogActivityService $logActivityService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
    }

    /**
     * Display the index view for Kecamatan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Kecamatan');
        return view('administration.masters.kecamatan.index');
    }

    /**
     * Retrieve and return the list of Kecamatan for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'search' => $request->input('search', ''),
        ];

        $query = Kecamatan::getFilters($filters);

        $this->logActivityService->log('Fetched Kecamatan list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_kecamatan . '" name="kecamatan_ids[]" value="' . $row->id_kecamatan . '">';
            })
            ->addColumn('aksi', function ($item) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $hasShow = $user->hasPermissionTo('administrator.master.kecamatan.show');
                $hasEdit = $user->hasPermissionTo('administrator.master.kecamatan.update');

                if (!$hasShow && !$hasEdit) {
                    return '<span class="text-muted">-</span>';
                }

                $btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $btn .= '<i class="fas fa-cogs"></i> Aksi';
                $btn .= '</button>';
                $btn .= '<div class="dropdown-menu">';

                if ($hasShow) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_kecamatan . '">';
                    $btn .= '<i class="fas fa-eye"></i> Lihat';
                    $btn .= '</a>';
                }

                if ($hasEdit) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_kecamatan . '">';
                    $btn .= '<i class="fas fa-edit"></i> Edit';
                    $btn .= '</a>';
                }

                $btn .= '</div></div>';
                return $btn;
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-primary' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->editColumn('kode_kecamatan', function ($item) {
                return '<span class="fw-bold">' . $item->kode_kecamatan . '</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'kode_kecamatan'])
            ->make(true);
    }

    /**
     * Store a new Kecamatan record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'kode_kecamatan' => 'required|string|max:20|unique:kecamatan,kode_kecamatan',
            'nama_kecamatan' => 'required|string|max:100|unique:kecamatan,nama_kecamatan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Kecamatan store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the Kecamatan
        $result = $this->transactionService->store($request, new Kecamatan(), $validationRules);

        $this->logActivityService->log('Stored new Kecamatan', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific Kecamatan by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $this->logActivityService->log('Viewed Kecamatan detail', 'ID: ' . $id);
        return $this->transactionService->getById(new Kecamatan(), $id);
    }

    /**
     * Update an existing Kecamatan record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $kecamatan = Kecamatan::find($id);

        if (!$kecamatan) {
            $this->logActivityService->log('Kecamatan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'kode_kecamatan' => 'required|string|max:20|unique:kecamatan,kode_kecamatan,' . $id . ',id_kecamatan',
            'nama_kecamatan' => 'required|string|max:100|unique:kecamatan,nama_kecamatan,' . $id . ',id_kecamatan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Kecamatan update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $kecamatan, $validationRules);

        $this->logActivityService->log('Updated Kecamatan', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing Kecamatan record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:kecamatan,id_kecamatan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Kecamatan', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the Kecamatan records by IDs
        $kecamatanRecords = Kecamatan::whereIn('id_kecamatan', $selectedIds)->get();

        if ($kecamatanRecords->isEmpty()) {
            $this->logActivityService->log('No Kecamatan records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($kecamatanRecords as $kecamatan) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $kecamatan, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Kecamatan', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }
}
