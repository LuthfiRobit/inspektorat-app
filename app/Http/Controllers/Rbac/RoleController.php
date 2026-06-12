<?php

namespace App\Http\Controllers\Rbac;

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
        $query = Role::query();

        // 🚀 Security: Jangan tampilkan role developer ke user dengan role selain developer.
        if (!auth()->user()->isDeveloper()) {
            $query->where('role_name', '!=', 'developer');
        }

        $roles = $query->get();

        LogActivityService::log('Fetched Role list');

        return DataTables::of($roles)
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
        $role = Role::find($id);
        if (!$role) {
            return $this->responseService->error('Data not found', 404);
        }

        // 🚀 Security: Jangan tampilkan detail role developer ke non-developer
        if ($role->role_name === 'developer' && !auth()->user()->isDeveloper()) {
            return $this->responseService->error('Data not found', 404);
        }

        LogActivityService::log('Viewed Role detail', 'ID: ' . $id);
        return $this->responseService->success($role);
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

        // 🚀 Security: Jangan tampilkan edit role developer ke non-developer
        if ($role->role_name === 'developer' && !auth()->user()->isDeveloper()) {
            abort(404);
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

        // 🚀 Security: Jangan biarkan non-developer update role developer
        if ($role->role_name === 'developer' && !auth()->user()->isDeveloper()) {
            return $this->responseService->error('Unauthorized to update developer role', 403);
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
        $role = Role::select('id_role', 'role_scope', 'role_name', 'role_description')->find($id);

        if (!$role) {
            return $this->responseService->error('Data not found', 404);
        }

        // 🚀 Security: Jangan tampilkan permission role developer ke non-developer
        if ($role->role_name === 'developer' && !auth()->user()->isDeveloper()) {
            return $this->responseService->error('Data not found', 404);
        }

        $permissionsQuery = Permission::select('id_permission', 'permission_name');

        // Hide rbac.permission.* permissions from non-developers
        if (!auth()->user()->isDeveloper()) {
            $permissionsQuery->where('permission_name', 'not like', 'rbac.permission.%');
        }

        $permissions = $permissionsQuery->get();
        $rolePermissions = RolePermission::where('role_id', $id)->pluck('permission_id');

        $groupedPermissions = [];

        foreach ($permissions as $p) {
            $parts = explode('.', $p->permission_name);
            
            if (count($parts) >= 3) {
                // e.g. master.kecamatan.view -> Modul: Master, Submodul: Kecamatan, Aksi: View
                // e.g. master.petugas.inspektorat.view -> Modul: Master, Submodul: Petugas Inspektorat, Aksi: View
                $modul = ucfirst($parts[0]);
                
                // Ambil semua bagian di tengah (dari index 1 sampai sebelum index terakhir)
                $middleParts = array_slice($parts, 1, count($parts) - 2);
                $submodul = ucwords(str_replace('-', ' ', implode(' ', $middleParts)));
                
                $aksi = ucfirst($parts[count($parts) - 1]);
            } elseif (count($parts) == 2) {
                // e.g. dashboard.view
                $modul = ucfirst($parts[0]);
                $submodul = ucfirst($parts[0]);
                $aksi = ucfirst($parts[1]);
            } else {
                // e.g. unknown
                $modul = 'Lainnya';
                $submodul = 'Umum';
                $aksi = $p->permission_name;
            }

            if (!isset($groupedPermissions[$modul])) {
                $groupedPermissions[$modul] = [];
            }
            if (!isset($groupedPermissions[$modul][$submodul])) {
                $groupedPermissions[$modul][$submodul] = [];
            }

            $groupedPermissions[$modul][$submodul][] = [
                'id' => $p->id_permission,
                'name' => $aksi,
                'slug' => $p->permission_name
            ];
        }

        $records = [
            'role' => $role,
            'permissions' => $groupedPermissions,
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

        // 🚀 Security: Jangan biarkan non-developer update permission role developer
        if ($role->role_name === 'developer' && !auth()->user()->isDeveloper()) {
            return $this->responseService->error('Unauthorized to update developer permissions', 403);
        }

        // Synchronize the selected permissions with the role
        // This will replace the existing permissions with the newly selected ones
        $permissionsToSync = $request->permissions;

        if (!auth()->user()->isDeveloper()) {
            // Get existing permission IDs for this role that match rbac.permission.*
            $existingRbacPermissionIds = $role->permissions()
                ->where('permission_name', 'like', 'rbac.permission.%')
                ->pluck('id_permission')
                ->toArray();

            // Filter out any permission IDs from the user input that represent rbac.permission.*
            // to prevent unauthorized injection.
            $allowedInputPermissionIds = Permission::whereIn('id_permission', $request->permissions)
                ->where('permission_name', 'not like', 'rbac.permission.%')
                ->pluck('id_permission')
                ->toArray();

            $permissionsToSync = array_merge($allowedInputPermissionIds, $existingRbacPermissionIds);
        }

        $role->permissions()->sync($permissionsToSync);

        LogActivityService::log('Stored Role permissions', 'Role ID: ' . $roleId . ' Permissions: ' . json_encode($request->permissions));

        return $this->responseService->success(null, 'Permissions successfully saved');
    }
}
