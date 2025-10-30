<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\User;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PetugasController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * PetugasController constructor.
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
     * Display the index view for Petugas.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $kecamatan = Kecamatan::where('status', 'active')
            ->orderBy('nama_kecamatan', 'ASC')
            ->get(['id_kecamatan', 'nama_kecamatan']);

        $users = User::where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get(['id_user', 'name', 'email']);

        $jabatanList = Petugas::getJabatanList();

        $this->logActivityService->log('Accessed the index view for Petugas');
        return view('administration.masters.petugas.index', compact('kecamatan', 'users', 'jabatanList'));
    }

    /**
     * Retrieve and return the list of Petugas for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status'    => $request->input('filter_status', ''),
            'filter_kecamatan' => $request->input('filter_kecamatan', ''),
            'filter_desa'      => $request->input('filter_desa', ''),
            'filter_jabatan'   => $request->input('filter_jabatan', ''),
            'search'           => $request->input('search', ''),
        ];

        $query = Petugas::getFilters($filters);

        $this->logActivityService->log('Fetched Petugas list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn(
                'checkbox',
                fn($row) =>
                '<input type="checkbox" class="table-checkbox form-check-input" name="petugas_ids[]" value="' . $row->id_petugas . '">'
            )
            ->addColumn('aksi', function ($item) {
                return '
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs"></i> Aksi
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
            ->editColumn(
                'nip',
                fn($item) =>
                $item->nip ? e($item->nip) : '<span class="text-muted">-</span>'
            )
            ->editColumn('nama_lengkap', function ($row) {
                if (strlen($row->nama_lengkap) > 30) {
                    return '<span data-bs-toggle="tooltip" title="' . e($row->nama_lengkap) . '">' .
                        e(Str::limit($row->nama_lengkap, 30)) . '</span>';
                }
                return e($row->nama_lengkap);
            })
            ->editColumn(
                'no_telp',
                fn($item) =>
                $item->no_telp ? e($item->no_telp) : '<span class="text-muted">-</span>'
            )
            ->editColumn('jabatan', function ($row) {
                if (strlen($row->jabatan) > 25) {
                    return '<span data-bs-toggle="tooltip" title="' . e($row->jabatan) . '">' .
                        e(Str::limit($row->jabatan, 25)) . '</span>';
                }
                return e($row->jabatan ?: '-');
            })
            ->addColumn('instansi', function ($item) {
                if ($item->nama_kecamatan && $item->nama_desa) {
                    return '<span>' . e($item->nama_desa) . '</span><br>
                        <small class="text-muted">Kec. ' . e($item->nama_kecamatan) . '</small>';
                } elseif ($item->nama_kecamatan) {
                    return '<small class="text-muted">Kec. ' . e($item->nama_kecamatan) . '</small>';
                } elseif ($item->nama_desa) {
                    return e($item->nama_desa);
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn(
                'unit_kerja',
                fn($item) =>
                $item->unit_kerja ? e($item->unit_kerja) : '<span class="text-muted">-</span>'
            )
            ->editColumn('status', function ($row) {
                $map = [
                    'active'    => ['class' => 'light badge-success', 'label' => 'AKTIF'],
                    'inactive'  => ['class' => 'light badge-danger', 'label' => 'NONAKTIF'],
                ];

                $badge = $map[$row->status] ?? ['class' => 'light badge-dark', 'label' => strtoupper($row->status ?? '-')];

                return '<span class="badge ' . $badge['class'] . '">' . $badge['label'] . '</span>';
            })
            ->rawColumns([
                'checkbox',
                'aksi',
                'nip',
                'nama_lengkap',
                'no_telp',
                'jabatan',
                'instansi',
                'unit_kerja',
                'status'
            ])
            ->make(true);
    }

    /**
     * Store a new Petugas record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'user_id' => 'nullable|exists:users,id_user',
            'kecamatan_id' => 'nullable|exists:kecamatan,id_kecamatan',
            'desa_id' => 'nullable|exists:desa,id_desa',
            'nama_lengkap' => 'required|string|max:100',
            'nip' => 'nullable|string|max:20|unique:petugas,nip',
            'jabatan' => 'nullable|string|max:100',
            'unit_kerja' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Petugas store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the Petugas
        $result = $this->transactionService->store($request, new Petugas(), $validationRules);

        $this->logActivityService->log('Stored new Petugas', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific Petugas by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $petugas = Petugas::with([
            'user:id_user,name,email',
            'kecamatan:id_kecamatan,nama_kecamatan',
            'desa:id_desa,nama_desa',
            'creator:id_user,name',
            'updater:id_user,name'
        ])
            ->find($id);

        if (!$petugas) {
            $this->logActivityService->log('Petugas not found for show', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $this->logActivityService->log('Viewed Petugas detail', 'ID: ' . $id);
        return $this->responseService->success($petugas, 'Data retrieved successfully');
    }

    /**
     * Update an existing Petugas record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $petugas = Petugas::find($id);

        if (!$petugas) {
            $this->logActivityService->log('Petugas not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'user_id' => 'nullable|exists:users,id_user',
            'kecamatan_id' => 'nullable|exists:kecamatan,id_kecamatan',
            'desa_id' => 'nullable|exists:desa,id_desa',
            'nama_lengkap' => 'required|string|max:100',
            'nip' => 'nullable|string|max:20|unique:petugas,nip,' . $id . ',id_petugas',
            'jabatan' => 'nullable|string|max:100',
            'unit_kerja' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Petugas update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $petugas, $validationRules);

        $this->logActivityService->log('Updated Petugas', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing Petugas record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:petugas,id_petugas',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Petugas', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the Petugas records by IDs
        $petugasRecords = Petugas::whereIn('id_petugas', $selectedIds)->get();

        if ($petugasRecords->isEmpty()) {
            $this->logActivityService->log('No Petugas records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($petugasRecords as $petugas) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $petugas, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Petugas', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Get desa by kecamatan ID for dropdown.
     *
     * @param int $kecamatanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDesaByKecamatan($kecamatanId)
    {
        $desa = Desa::where('kecamatan_id', $kecamatanId)
            ->where('status', 'active')
            ->orderBy('nama_desa', 'ASC')
            ->get(['id_desa', 'nama_desa']);

        $this->logActivityService->log('Fetched Desa by Kecamatan for Petugas', 'Kecamatan ID: ' . $kecamatanId);
        return $this->responseService->success($desa, 'Data retrieved successfully');
    }

    /**
     * Get petugas by kecamatan ID for dropdown.
     *
     * @param int $kecamatanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByKecamatan($kecamatanId)
    {
        $petugas = Petugas::where('kecamatan_id', $kecamatanId)
            ->where('status', 'active')
            ->orderBy('nama_lengkap', 'ASC')
            ->get(['id_petugas', 'nama_lengkap', 'jabatan']);

        $this->logActivityService->log('Fetched Petugas by Kecamatan', 'Kecamatan ID: ' . $kecamatanId);
        return $this->responseService->success($petugas, 'Data retrieved successfully');
    }

    /**
     * Get petugas by desa ID for dropdown.
     *
     * @param int $desaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDesa($desaId)
    {
        $petugas = Petugas::where('desa_id', $desaId)
            ->where('status', 'active')
            ->orderBy('nama_lengkap', 'ASC')
            ->get(['id_petugas', 'nama_lengkap', 'jabatan']);

        $this->logActivityService->log('Fetched Petugas by Desa', 'Desa ID: ' . $desaId);
        return $this->responseService->success($petugas, 'Data retrieved successfully');
    }
}
