<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected $responseService;
    protected $transactionService;

    /**
     * UserController constructor.
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
     * Display the index view for User.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        LogActivityService::log('Accessed the User Management index');
        return view('administration.rbac.user.index');
    }

    /**
     * Retrieve and return the list of User for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
        ];

        $query = User::query();

        // 🚀 Pastikan user dengan role developer tidak ditampilkan ke user dengan role selain developer.
        if (!auth()->user()->hasRole('developer')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('role_name', 'developer');
            });
        }

        if ($filters['filter_status']) {
            $query->where('status', $filters['filter_status']);
        }

        LogActivityService::log('Fetched user list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', fn($row) => '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_user . '" name="user_ids[]" value="' . $row->id_user . '">')
            ->addColumn('aksi', function ($user) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cogs"></i> Aksi
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" data-id="' . $user->id_user . '" data-action="action_show">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-id="' . $user->id_user . '" data-action="action_edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-id="' . $user->id_user . '" data-action="action_role">
                                    <i class="fas fa-user-shield"></i> Role
                                </a>
                            </div>
                        </div>';
            })
            ->editColumn('status', function ($item) {
                $checked = ($item->status == 'active') ? 'checked' : '';
                // $label = ($item->status == 'active') ? 'Aktif' : 'Tidak Aktif';

                return '<div class="form-check form-switch">
                            <input class="form-check-input datatable-status-switcher" type="checkbox" role="switch" id="switch_' . $item->id_user . '" ' . $checked . ' data-id="' . $item->id_user . '">
                            <label class="form-check-label" for="switch_' . $item->id_user . '">' . $item->status . '</label>
                        </div>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status'])
            ->make(true);
    }

    /**
     * Store a new User record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors());
        }

        $data = $request->only(['name', 'username', 'email', 'status']);
        $data['password'] = Hash::make($request->password);

        $user = new User($data);
        $user->save();

        LogActivityService::log('Created user', 'User: ' . json_encode($user));

        return $this->responseService->success($user, 'User created successfully');
    }

    /**
     * Display the details of a specific User by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return $this->responseService->error('User not found', 404);
        }

        // 🚀 Security: Jangan tampilkan detail developer ke non-developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('User not found', 404);
        }

        LogActivityService::log('Viewed user', 'ID: ' . $id);

        return $this->responseService->success($user);
    }

    /**
     * Update an existing User record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username,' . $id . ',id_user',
            'email' => 'required|email|unique:users,email,' . $id . ',id_user',
            'status' => 'required|in:active,inactive',
        ];

        if ($request->filled('password')) {
            $validationRules['password'] = 'required|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            LogActivityService::log('Validation failed during User update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $user = User::find($id);
        if (!$user) {
            LogActivityService::log('User not found for update', 'ID: ' . $id);
            return $this->responseService->error('User not found', 404);
        }

        // 🚀 Security: Jangan biarkan non-developer update akun developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('Unauthorized to update developer user', 403);
        }

        // 🚀 FIX: hapus field password kalau kosong agar tidak di-overwrite null
        if (!$request->filled('password')) {
            $request->request->remove('password');
        }

        $result = $this->transactionService->update(
            $request,
            $user,
            $validationRules,
            function ($request, $model) {
                if ($request->filled('password')) {
                    $model->password = Hash::make($request->password);
                }
            }
        );

        LogActivityService::log('Updated user', 'ID: ' . $id . ' Data: ' . json_encode($request->except(['password', 'password_confirmation'])));

        return $result;
    }


    /**
     * Update the status of a single user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $validationRules = [
            'id' => 'required|exists:users,id_user',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log('Validation failed on user status change', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $user = User::where('id_user', $request->input('id'))->first();

        if (!$user) {
            LogActivityService::log('User not found for status change', 'ID: ' . $request->input('id'));
            return $this->responseService->error('User not found', ResponseService::STATUS_NOT_FOUND);
        }

        // 🚀 Security: Jangan biarkan non-developer update status akun developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('Unauthorized to update developer status', 403);
        }

        // Inject status ke request (supaya transactionService bisa memproses)
        $request->merge(['status' => $request->input('status')]);

        // Gunakan transactionService
        $this->transactionService->update($request, $user, $validationRules);

        LogActivityService::log('User status changed using transaction service', 'ID: ' . $user->id_user . ' Status: ' . $user->status);

        return $this->responseService->success(null, 'Status updated successfully');
    }


    /**
     * Update status multiple an existing User record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:users,id_user',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log('Validation failed during update status for multiple User', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the User records by IDs
        $query = User::whereIn('id_user', $selectedIds);

        // 🚀 Security: Filter akun developer jika yang mengupdate bukan developer
        if (!auth()->user()->hasRole('developer')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('role_name', 'developer');
            });
        }

        $userRecords = $query->get();

        if ($userRecords->isEmpty()) {
            LogActivityService::log('No User records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($userRecords as $user) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $user, $validationRules);
        }

        LogActivityService::log('Updated status for multiple User', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Retrieve and return the list of Roles for a User by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUserRole($id)
    {
        // Ambil data user
        $user = User::select('id_user', 'name', 'email')->find($id);

        if (!$user) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // 🚀 Security: Jangan tampilkan role developer ke non-developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('Data not found', 404);
        }

        $roles = Role::select('id_role', 'role_name')->get();

        // 🚀 Security: Jangan tampilkan role developer sebagai opsi jika user saat ini bukan developer
        if (!auth()->user()->hasRole('developer')) {
            $roles = $roles->where('role_name', '!=', 'developer');
        }

        $userRoles = DB::table('user_role')->where('user_id', $id)->pluck('role_id');

        $records = [
            'user' => $user,
            'roles' => $roles,
            'user_roles' => $userRoles,
        ];

        LogActivityService::log('Fetched User and Role list in JSON', "User ID: $id");

        return $this->responseService->success($records);
    }

    /**
     * Store the Roles for a specific User.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUserRole(Request $request, $userId)
    {
        // Validasi input
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:role,id_role', // Pastikan semua ID role valid
        ]);

        // Temukan user
        $user = User::findOrFail($userId);

        // 🚀 Security: Jangan biarkan non-developer update role akun developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('Unauthorized to update developer roles', 403);
        }

        // 🚀 Security: Jangan biarkan assign role developer jika user saat ini bukan developer
        if (!auth()->user()->hasRole('developer')) {
            $developerRole = Role::where('role_name', 'developer')->first();
            if ($developerRole && in_array($developerRole->id_role, $request->roles)) {
                return $this->responseService->error('Cannot assign developer role', 403);
            }
        }

        // Sinkronisasi role
        // Ini akan menggantikan semua role yang ada dengan yang baru
        $user->roles()->sync($request->roles);

        LogActivityService::log('Stored User roles', 'User ID: ' . $userId . ' Roles: ' . json_encode($request->roles));

        return $this->responseService->success(null, 'Roles successfully saved');
    }


    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:role,id_role',
        ]);

        $user = User::find($id);
        if (!$user) {
            return $this->responseService->error('User not found', 404);
        }

        // 🚀 Security: Jangan biarkan non-developer assign role ke akun developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('Unauthorized to assign roles to developer', 403);
        }

        // 🚀 Security: Jangan biarkan assign role developer jika user saat ini bukan developer
        if (!auth()->user()->hasRole('developer')) {
            $developerRole = Role::where('role_name', 'developer')->first();
            if ($developerRole && in_array($developerRole->id_role, $request->role_ids)) {
                return $this->responseService->error('Cannot assign developer role', 403);
            }
        }

        $user->roles()->syncWithoutDetaching($request->role_ids);

        LogActivityService::log('Assigned roles to user', 'User ID: ' . $id . ' Roles: ' . json_encode($request->role_ids));

        return $this->responseService->success(null, 'Roles assigned successfully');
    }

    public function revokeRole(Request $request, $id)
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:role,id_role',
        ]);

        $user = User::find($id);
        if (!$user) {
            return $this->responseService->error('User not found', 404);
        }

        // 🚀 Security: Jangan biarkan non-developer revoke role dari akun developer
        if ($user->hasRole('developer') && !auth()->user()->hasRole('developer')) {
            return $this->responseService->error('Unauthorized to revoke roles from developer', 403);
        }

        $user->roles()->detach($request->role_ids);

        LogActivityService::log('Revoked roles from user', 'User ID: ' . $id . ' Roles: ' . json_encode($request->role_ids));

        return $this->responseService->success(null, 'Roles revoked successfully');
    }
}
