<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\JenisKegiatan;
use App\Models\TahunAnggaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class JenisKegiatanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * JenisKegiatanController constructor.
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
     * Display the index view for JenisKegiatan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('administration.masters.jenisKegiatan.index');
    }

    /**
     * Retrieve and return the list of JenisKegiatan for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tahun' => $request->input('filter_tahun', '')
        ];

        $query = JenisKegiatan::getFilters($filters); // sekarang mengembalikan Collection biasa dari DB Query Builder

        $this->logActivityService->log('Fetched Jenis Kegiatan list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_jenis_kegiatan . '" name="jenis_ids[]" value="' . $row->id_jenis_kegiatan . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cogs"></i>  Aksi
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_jenis_kegiatan . '">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_jenis_kegiatan . '">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-primary' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->addColumn('tahun_anggaran', function ($item) {
                return $item->tahun ?? '<span class="text-muted">-</span>'; // langsung ambil dari join
            })
            ->editColumn('kode_jenis', function ($item) {
                return '<span class="fw-bold">' . $item->kode_jenis . '</span>';
            })
            ->editColumn('nama_jenis', function ($item) {
                return '<div class="fw-bold">' . $item->nama_jenis . '</div>';
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'tahun_anggaran', 'kode_jenis', 'nama_jenis'])
            ->make(true);
    }

    /**
     * Store a new JenisKegiatan record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id_tahun_anggaran',
            'kode_jenis'        => 'required|string|max:10|unique:jenis_kegiatan,kode_jenis,NULL,id_jenis_kegiatan,tahun_anggaran_id,' . $request->input('tahun_anggaran_id'),
            'nama_jenis'        => 'required|string|max:100|unique:jenis_kegiatan,nama_jenis,NULL,id_jenis_kegiatan,tahun_anggaran_id,' . $request->input('tahun_anggaran_id'),
            'keterangan'        => 'nullable|string|max:500',
            'status'            => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Jenis Kegiatan store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to store the JenisKegiatan
        $result = $this->transactionService->store($request, new JenisKegiatan(), $validationRules);

        $this->logActivityService->log('Stored new Jenis Kegiatan', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific JenisKegiatan by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $jenisKegiatan = JenisKegiatan::getRelationship($id);
        if (!$jenisKegiatan) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $this->logActivityService->log('Viewed Jenis Kegiatan detail', 'ID: ' . $id);
        return $this->responseService->success($jenisKegiatan, 'Data retrieved successfully');
    }

    /**
     * Update an existing JenisKegiatan record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $jenisKegiatan = JenisKegiatan::find($id);

        if (!$jenisKegiatan) {
            $this->logActivityService->log('Jenis Kegiatan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $validationRules = [
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id_tahun_anggaran',
            'kode_jenis'        => 'required|string|max:10|unique:jenis_kegiatan,kode_jenis,' . $id . ',id_jenis_kegiatan,tahun_anggaran_id,' . $request->input('tahun_anggaran_id'),
            'nama_jenis'        => 'required|string|max:100|unique:jenis_kegiatan,nama_jenis,' . $id . ',id_jenis_kegiatan,tahun_anggaran_id,' . $request->input('tahun_anggaran_id'),
            'keterangan'        => 'nullable|string|max:500',
            'status'            => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Jenis Kegiatan update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $jenisKegiatan, $validationRules);

        $this->logActivityService->log('Updated Jenis Kegiatan', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing JenisKegiatan record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:jenis_kegiatan,id_jenis_kegiatan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Jenis Kegiatan', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the JenisKegiatan records by IDs
        $jenisKegiatanRecords = JenisKegiatan::whereIn('id_jenis_kegiatan', $selectedIds)->get();

        if ($jenisKegiatanRecords->isEmpty()) {
            $this->logActivityService->log('No Jenis Kegiatan records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($jenisKegiatanRecords as $jenisKegiatan) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $jenisKegiatan, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Jenis Kegiatan', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Get jenis kegiatan by tahun anggaran ID for dropdown.
     *
     * @param int $tahunAnggaranId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByTahunAnggaran($tahunAnggaranId)
    {
        $jenisKegiatan = JenisKegiatan::where('tahun_anggaran_id', $tahunAnggaranId)
            ->where('status', 'active')
            ->orderBy('kode_jenis', 'ASC')
            ->get(['id_jenis_kegiatan', 'kode_jenis', 'nama_jenis']);

        $this->logActivityService->log('Fetched Jenis Kegiatan by Tahun Anggaran', 'Tahun Anggaran ID: ' . $tahunAnggaranId);
        return $this->responseService->success($jenisKegiatan, 'Data retrieved successfully');
    }
}
