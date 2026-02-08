<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DesaController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * DesaController constructor.
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
     * Display the index view for Desa.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Desa');
        return view('administration.masters.desa.index');
    }

    /**
     * Retrieve and return the list of Desa for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_kecamatan' => $request->input('filter_kecamatan', ''),
            'search' => $request->input('search', ''),
        ];

        $query = Desa::getFilters($filters);

        $this->logActivityService->log('Fetched Desa list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_desa . '" name="desa_ids[]" value="' . $row->id_desa . '">';
            })
            ->addColumn('aksi', function ($item) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $hasShow = $user->hasPermissionTo('administrator.master.desa.show');
                $hasEdit = $user->hasPermissionTo('administrator.master.desa.update');

                if (!$hasShow && !$hasEdit) {
                    return '<span class="text-muted">-</span>';
                }

                $btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $btn .= '<i class="fas fa-cogs"></i> Aksi';
                $btn .= '</button>';
                $btn .= '<div class="dropdown-menu">';

                if ($hasShow) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_desa . '">';
                    $btn .= '<i class="fas fa-eye"></i> Lihat';
                    $btn .= '</a>';
                }

                if ($hasEdit) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_desa . '">';
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
            ->editColumn('kode_desa', function ($item) {
                return '<span class="fw-bold">' . $item->kode_desa . '</span>';
            })
            ->addColumn('nama_kecamatan', function ($item) {
                return $item->nama_kecamatan ? $item->nama_kecamatan : '<span class="text-muted">-</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'kode_desa', 'nama_kecamatan'])
            ->make(true);
    }

    /**
     * Store a new Desa record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kecamatan'],
            'kode_desa' => ['required', 'string', 'max:10', 'unique:desa,kode_desa'],
            'nama_desa' => [
                'required',
                'string',
                'max:100',
                Rule::unique('desa')->where(function ($query) use ($request) {
                    return $query->where('kecamatan_id', $request->input('kecamatan_id'));
                }),
            ],
            'status' => ['required', 'in:active,inactive'],
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Desa store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the Desa
        $result = $this->transactionService->store($request, new Desa(), $validationRules);

        $this->logActivityService->log('Stored new Desa', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific Desa by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Ambil data desa beserta nama kecamatan
        $desa = Desa::getRelationship($id);

        if (!$desa) {
            $this->logActivityService->log('Desa not found for show', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $this->logActivityService->log('Viewed Desa detail', 'ID: ' . $id);
        return $this->responseService->success($desa, 'Data retrieved successfully');
    }

    /**
     * Update an existing Desa record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $desa = Desa::find($id);

        if (!$desa) {
            $this->logActivityService->log('Desa not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kecamatan'],
            'kode_desa' => ['required', 'string', 'max:10', Rule::unique('desa', 'kode_desa')->ignore($id, 'id_desa')],
            'nama_desa' => [
                'required',
                'string',
                'max:100',
                Rule::unique('desa')->ignore($id, 'id_desa')->where(function ($query) use ($request) {
                    return $query->where('kecamatan_id', $request->input('kecamatan_id'));
                }),
            ],
            'status' => ['required', 'in:active,inactive'],
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Desa update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $desa, $validationRules);

        $this->logActivityService->log('Updated Desa', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing Desa record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:desa,id_desa',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Desa', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the Desa records by IDs
        $desaRecords = Desa::whereIn('id_desa', $selectedIds)->get();

        if ($desaRecords->isEmpty()) {
            $this->logActivityService->log('No Desa records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($desaRecords as $desa) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $desa, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Desa', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Get desa by kecamatan ID for dropdown.
     *
     * @param int $kecamatanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByKecamatan($kecamatanId)
    {
        $desa = Desa::where('kecamatan_id', $kecamatanId)
            ->where('status', 'active')
            ->orderBy('nama_desa', 'ASC')
            ->get(['id_desa', 'nama_desa']);

        $this->logActivityService->log('Fetched Desa by Kecamatan', 'Kecamatan ID: ' . $kecamatanId);
        return $this->responseService->success($desa, 'Data retrieved successfully');
    }
}
