<?php

namespace App\Http\Controllers\RBAC;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    protected $transactionService;
    protected $responseService;

    /**
     * RoleController constructor.
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
     * Display the index view for Role.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        LogActivityService::log('Accessed the index view for Role');
        return view('administration.rbac.role.index');
    }

    /**
     * Retrieve and return the list of Roles for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $query = Role::get();

        LogActivityService::log('Fetched Role list');

        return DataTables::of($query)
            ->addColumn('checkbox', fn($row) => '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_role . '" name="role_ids[]" value="' . $row->id_role . '">')
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i>  Actions
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_role . '">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_role . '">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_permission" data-id="' . $item->id_role . '">
                                    <i class="fas fa-shield-alt"></i> Permissions
                                </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
    }

    /**
     * Store a new Role record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'role_name' => 'required|string|max:255|unique:role,role_name',
            'role_description' => 'required|string',
            'role_scope' => 'nullable|in:inspektorat,kecamatan,desa',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log('Validation failed during Role store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the Role
        $result = $this->transactionService->store($request, new Role(), $validationRules);

        LogActivityService::log('Stored new Role', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific Role by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        LogActivityService::log('Viewed Role detail', 'ID: ' . $id);
        return $this->transactionService->getById(new Role(), $id);
    }

    /**
     * Display the edit view for Role.
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }
        LogActivityService::log('Accessed the edit permission view for Role');
        return view('administration.rbac.role.edit', compact('role'));
    }

    /**
     * Update an existing Role record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validationRules = [
            'role_name' => 'required|string|max:255|unique:role,role_name,' . $id . ',id_role',
            'role_description' => 'required|string',
            'role_scope' => 'nullable|in:inspektorat,kecamatan,desa',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log('Validation failed during Role update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Find the Role record by ID
        $role = Role::find($id);

        if (!$role) {
            LogActivityService::log('Role not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $role, $validationRules);

        LogActivityService::log('Updated Role', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Retrieve and return the list of Permissions for a Role by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listRolePermission($id)
    {
        // Fetch the role and associated permissions
        $role = Role::select('role_scope', 'role_name', 'role_description')->find($id);
        $permissions = Permission::select('id_permission', 'permission_name')->get();
        $rolePermissions = RolePermission::where('role_id', $id)->pluck('permission_id');

        $records = [
            'role' => $role,
            'permissions' => $permissions,
            'role_permissions' => $rolePermissions,
        ];

        LogActivityService::log('Fetched Role and Permissions list in JSON');

        return $this->responseService->success($records);
    }

    /**
     * Store the Permissions for a specific Role.
     *
     * @param Request $request
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeRolePermission(Request $request, $roleId)
    {
        // Validate that the permissions are an array and not empty
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permission,id_permission', // Ensure each permission ID is valid
        ]);

        // Find the Role by ID
        $role = Role::findOrFail($roleId);

        // Synchronize the selected permissions with the role
        // This will replace the existing permissions with the newly selected ones
        $role->permissions()->sync($request->permissions);

        LogActivityService::log('Stored Role permissions', 'Role ID: ' . $roleId . ' Permissions: ' . json_encode($request->permissions));

        return $this->responseService->success(null, 'Permissions successfully saved');
    }
}
