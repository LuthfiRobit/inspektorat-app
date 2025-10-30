<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use App\Services\UserAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

abstract class BasePetugasController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $userAccountService;
    protected $scope;

    public function __construct(ResponseService $responseService, TransactionService $transactionService, UserAccountService $userAccountService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->userAccountService = $userAccountService;
    }

    /**
     * Get validation rules for specific scope
     */
    abstract protected function getValidationRules($id = null): array;

    /**
     * Get view directory for specific scope
     */
    abstract protected function getViewDirectory(): string;

    /**
     * Get query with scope-specific filters
     */
    abstract protected function getScopeQuery();

    /**
     * Display the index view for Petugas.
     */
    public function index()
    {
        LogActivityService::log("Accessed the index view for Petugas {$this->scope}");
        return view("administration.masters.petugas.{$this->getViewDirectory()}.index");
    }

    /**
     * Retrieve and return the list of Petugas for DataTables.
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
        ];

        $query = $this->getScopeQuery()->getFilters($filters);

        LogActivityService::log("Fetched Petugas {$this->scope} list", 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" 
                class="petugas-checkbox form-check-input" 
                id="checkbox_' . $row->id_petugas . '" 
                name="petugas_ids[]" 
                value="' . $row->id_petugas . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i>  Aksi
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_petugas . '">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_petugas . '">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-primary' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status'])
            ->make(true);
    }

    /**
     * Display the create view for Petugas.
     */
    public function create()
    {
        LogActivityService::log("Accessed the create view for Petugas {$this->scope}");
        return view("administration.masters.petugas.{$this->getViewDirectory()}.create");
    }

    /**
     * Store a new Petugas record in the database.
     */
    public function store(Request $request)
    {
        $validationRules = $this->getValidationRules();

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log("Validation failed during Petugas {$this->scope} store", 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $fileFields = [];
        $oldFiles = [];

        if ($request->hasFile('foto_petugas')) {
            $fileFields = [
                'foto_petugas' => 'petugas/foto',
            ];
        }

        LogActivityService::log("Stored new Petugas {$this->scope}", 'Data: ' . json_encode($request->all()));

        return $this->transactionService->store(
            $request,
            new Petugas(),
            $validationRules,
            function ($request, $petugas) {
                // Set scope-specific data
                $this->setScopeData($petugas, $request);

                // Create user account
                $user = $this->userAccountService->createPetugasAccount($petugas, $this->scope, $request->status);
                $petugas->user_id = $user->id_user;
                $petugas->save();
            },
            $fileFields,
            $oldFiles
        );
    }

    /**
     * Display the details of a specific Petugas by ID.
     */
    public function show($id)
    {
        $petugas = $this->getScopeQuery()->find($id);

        if (!$petugas) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        LogActivityService::log("Viewed Petugas {$this->scope} detail", 'ID: ' . $id);
        return $this->responseService->success($petugas);
    }

    /**
     * Display the edit view for Petugas.
     */
    public function edit($id)
    {
        LogActivityService::log("Accessed the edit view for Petugas {$this->scope}");

        $petugas = $this->getScopeQuery()->find($id);
        return view("administration.masters.petugas.{$this->getViewDirectory()}.edit", compact('petugas'));
    }

    /**
     * Update an existing Petugas record.
     */
    public function update(Request $request, $id)
    {
        $validationRules = $this->getValidationRules($id);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            LogActivityService::log("Validation failed during Petugas {$this->scope} update", 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $petugas = $this->getScopeQuery()->find($id);

        if (!$petugas) {
            LogActivityService::log("Petugas {$this->scope} not found for update", 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $fileFields = [];
        $oldFiles = [];

        if ($request->hasFile('foto_petugas')) {
            $fileFields = [
                'foto_petugas' => 'petugas/foto',
            ];

            // Delete old foto if exists
            if ($petugas->foto_petugas) {
                $oldFiles = [
                    'foto_petugas' => $petugas->foto_petugas,
                ];
            }
        }

        LogActivityService::log("Updated Petugas {$this->scope}", 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $this->transactionService->update(
            $request,
            $petugas,
            $validationRules,
            function ($request, $petugas) {
                // Set scope-specific data
                $this->setScopeData($petugas, $request);

                // Update user account
                $this->userAccountService->updatePetugasAccount($petugas, $this->scope, $request->status);
            },
            $fileFields,
            $oldFiles
        );
    }

    /**
     * Set scope-specific data for petugas
     */
    abstract protected function setScopeData($petugas, $request);
}
