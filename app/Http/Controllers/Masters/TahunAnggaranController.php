<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\TahunAnggaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TahunAnggaranController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * TahunAnggaranController constructor.
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
     * Display the index view for TahunAnggaran.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $currentYear = date('Y');
        $yearsRange = range($currentYear - 10, $currentYear + 10);

        $this->logActivityService->log('Accessed the index view for Tahun Anggaran');
        return view('administration.masters.tahunAnggaran.index', compact('yearsRange'));
    }

    /**
     * Retrieve and return the list of TahunAnggaran for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'search' => $request->input('search', ''),
            'tahun_from' => $request->input('tahun_from', ''),
            'tahun_to' => $request->input('tahun_to', ''),
        ];

        $query = TahunAnggaran::getFilters($filters);

        $this->logActivityService->log('Fetched Tahun Anggaran list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_tahun_anggaran . '" name="tahun_ids[]" value="' . $row->id_tahun_anggaran . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i>  Aksi
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_tahun_anggaran . '">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_tahun_anggaran . '">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-action="action_activate" data-id="' . $item->id_tahun_anggaran . '">
                                    <i class="fas fa-check-circle"></i> ' . ($item->status == 'active' ? 'Nonaktifkan' : 'Aktifkan') . '
                                </a>
                            </div>
                        </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-primary' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->editColumn('tahun', function ($item) {
                return '<span class="fw-bold">' . $item->tahun . '</span>';
            })
            ->editColumn('keterangan', function ($item) {
                return $item->keterangan ?: '<span class="text-muted">-</span>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'tahun', 'keterangan'])
            ->make(true);
    }

    /**
     * Store a new TahunAnggaran record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $currentYear = date('Y');
        $validationRules = [
            'tahun' => 'required|integer|min:' . ($currentYear - 10) . '|max:' . ($currentYear + 10) . '|unique:tahun_anggaran,tahun',
            'keterangan' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request input
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Tahun Anggaran store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // If activating this year, deactivate others
        if ($request->status === 'active') {
            TahunAnggaran::where('status', 'active')->update(['status' => 'inactive']);
        }

        // Use TransactionService to store the TahunAnggaran
        $result = $this->transactionService->store($request, new TahunAnggaran(), $validationRules);

        $this->logActivityService->log('Stored new Tahun Anggaran', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific TahunAnggaran by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $this->logActivityService->log('Viewed Tahun Anggaran detail', 'ID: ' . $id);
        return $this->transactionService->getById(new TahunAnggaran(), $id);
    }

    /**
     * Update an existing TahunAnggaran record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $tahunAnggaran = TahunAnggaran::find($id);

        if (!$tahunAnggaran) {
            $this->logActivityService->log('Tahun Anggaran not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $currentYear = date('Y');
        $validationRules = [
            'tahun' => 'required|integer|min:' . ($currentYear - 10) . '|max:' . ($currentYear + 10) . '|unique:tahun_anggaran,tahun,' . $id . ',id_tahun_anggaran',
            'keterangan' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Tahun Anggaran update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // If activating this year, deactivate others
        if ($request->status === 'active' && $tahunAnggaran->status !== 'active') {
            TahunAnggaran::where('status', 'active')->update(['status' => 'inactive']);
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $tahunAnggaran, $validationRules);

        $this->logActivityService->log('Updated Tahun Anggaran', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Activate a specific TahunAnggaran.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($id)
    {
        $tahunAnggaran = TahunAnggaran::find($id);

        if (!$tahunAnggaran) {
            $this->logActivityService->log('Tahun Anggaran not found for activation', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Deactivate all other years
        TahunAnggaran::where('id_tahun_anggaran', '!=', $id)->update(['status' => 'inactive']);

        // Activate the selected year
        $tahunAnggaran->update(['status' => 'active']);

        $this->logActivityService->log('Activated Tahun Anggaran', 'ID: ' . $id . ' Tahun: ' . $tahunAnggaran->tahun);

        return $this->responseService->success($tahunAnggaran, 'Tahun anggaran berhasil diaktifkan');
    }

    /**
     * Update status multiple TahunAnggaran records.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:tahun_anggaran,id_tahun_anggaran',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Tahun Anggaran', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // If activating, deactivate all others first
        if ($newStatus === 'active') {
            TahunAnggaran::whereNotIn('id_tahun_anggaran', $selectedIds)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }

        // Find the TahunAnggaran records by IDs
        $tahunRecords = TahunAnggaran::whereIn('id_tahun_anggaran', $selectedIds)->get();

        if ($tahunRecords->isEmpty()) {
            $this->logActivityService->log('No Tahun Anggaran records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($tahunRecords as $tahun) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $tahun, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Tahun Anggaran', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Get active tahun anggaran.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActive()
    {
        $activeYear = TahunAnggaran::getActive();

        $this->logActivityService->log('Fetched active Tahun Anggaran');
        return $this->responseService->success($activeYear, 'Active tahun anggaran retrieved successfully');
    }
}
