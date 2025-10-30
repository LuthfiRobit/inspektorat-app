<?php

namespace App\Http\Controllers\RBAC;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    protected $transactionService;
    protected $responseService;

    /**
     * PermissionController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
    }

    /**
     * Display the index view for Permission.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        LogActivityService::log('Accessed the index view for Permission');
        return view('administration.rbac.permission.index');
    }

    /**
     * Retrieve and return the list of Permission for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $query = Permission::get();

        LogActivityService::log('Fetched Permission list');

        return DataTables::of($query)
            ->addColumn('checkbox', fn($row) => '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_permission . '" name="permission_ids[]" value="' . $row->id_permission . '">')

            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cogs"></i>  Aksi
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_permission . '">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_permission . '">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>';
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
    }

    /**
     * Store a new Permission record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'permission_name' => 'required|string|max:255|unique:permission,permission_name', // added unique validation
            'permission_description' => 'required|string',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log('Validation failed during Permission store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the Permission
        $result = $this->transactionService->store($request, new Permission(), $validationRules);

        LogActivityService::log('Stored new Permission', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific Permission by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        LogActivityService::log('Viewed Permission detail', 'ID: ' . $id);
        return $this->transactionService->getById(new Permission(), $id);
    }

    /**
     * Update an existing Permission record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validationRules = [
            'permission_name' => 'required|string|max:255|unique:permission,permission_name,' . $id . ',id_permission', // added unique validation, excluding current permission by ID
            'permission_description' => 'required|string',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log('Validation failed during Permission update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Find the Permission record by ID
        $permission = Permission::find($id);

        if (!$permission) {
            LogActivityService::log('Permission not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $permission, $validationRules);

        LogActivityService::log('Updated Permission', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Retrieve and return the list of Permission for Json.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listPemission()
    {
        $records = Permission::select('id_permission', 'permission_name')->get();
        return $this->responseService->success($records);
    }
}
