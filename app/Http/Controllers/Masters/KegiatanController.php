<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\TahunAnggaran;
use App\Models\JenisKegiatan;
use App\Models\PertanyaanKegiatan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class KegiatanController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * KegiatanController constructor.
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
     * Display the index view for Kegiatan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tahunAnggaran = TahunAnggaran::orderBy('tahun', 'DESC')
            ->get(['id_tahun_anggaran', 'tahun']);

        $jenisKegiatan = JenisKegiatan::where('status', 'active')
            ->orderBy('kode_jenis', 'ASC')
            ->get(['id_jenis_kegiatan', 'kode_jenis', 'nama_jenis']);

        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $this->logActivityService->log('Accessed the index view for Kegiatan');
        return view('administration.masters.kegiatan.index', compact('tahunAnggaran', 'jenisKegiatan', 'bulanList'));
    }

    /**
     * Retrieve and return the list of Kegiatan for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_tahun' => $request->input('filter_tahun', ''),
            'filter_jenis' => $request->input('filter_jenis', ''),
            'filter_bulan' => $request->input('filter_bulan', ''),
            'search' => $request->input('search', ''),
        ];

        $query = Kegiatan::getFilters($filters);
        // return $query;

        $this->logActivityService->log('Fetched Kegiatan list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-checkbox form-check-input" id="checkbox_' . $row->id_kegiatan . '" name="kegiatan_ids[]" value="' . $row->id_kegiatan . '">';
            })
            ->addColumn('aksi', function ($item) {
                return '<div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cogs"></i>  Aksi
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item action-trigger" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_kegiatan . '">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a class="dropdown-item action-trigger" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_kegiatan . '">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>';
            })
            ->editColumn('status', function ($item) {
                $badgeClass = ($item->status == 'active') ? 'light badge-success' : 'light badge-danger';
                return '<span class="fs-7 badge ' . $badgeClass . '">' . strtoupper($item->status) . '</span>';
            })
            ->editColumn('nama_kegiatan', function ($item) {
                return Str::limit($item->nama_kegiatan, 40);
            })
            ->rawColumns(['checkbox', 'aksi', 'status', 'nama_kegiatan'])
            ->make(true);
    }

    /**
     * Store a new Kegiatan record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Store a new Kegiatan record in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi
        $validationRules = [
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id_tahun_anggaran',
            'jenis_kegiatan_id' => 'required|exists:jenis_kegiatan,id_jenis_kegiatan',
            'kode_kegiatan' => 'required|string|max:20',
            'nama_kegiatan' => 'required|string|max:500',

            // Logic Validasi Baru
            'frekuensi_pelaporan' => 'nullable|integer|in:1,2,3,4,6,12', // Jika ada, berarti Rutin

            // Insidentil
            'bulan' => 'nullable|required_without:frekuensi_pelaporan|integer|min:1|max:12',
            'tanggal_mulai_insidentil' => 'nullable|required_without:frekuensi_pelaporan|integer|min:1|max:31',
            'tanggal_selesai_insidentil' => 'nullable|required_without:frekuensi_pelaporan|integer|min:1|max:31',

            // Rutin
            'bulan_mulai' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:12',
            'bulan_selesai' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:12|gte:bulan_mulai',
            'tanggal_mulai_rutin' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:31',
            'tanggal_selesai_rutin' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:31',

            'batas_akhir_upload' => 'required|integer|min:1|max:31',
            'dasar_hukum' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];

        // Custom validation for unique kode_kegiatan per tahun_anggaran_id
        $validator = Validator::make($request->all(), $validationRules);
        $validator->after(function ($validator) use ($request) {
            if (!Kegiatan::isKodeUnique($request->kode_kegiatan, $request->tahun_anggaran_id)) {
                $validator->errors()->add('kode_kegiatan', 'Kode kegiatan sudah digunakan untuk tahun anggaran ini.');
            }
        });

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Kegiatan store', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // --- Data Mapping Logic (Rutin vs Insidentil) ---
        if ($request->filled('frekuensi_pelaporan')) {
            // Skenario Rutin
            $request->merge([
                'bulan' => $request->bulan_mulai, // Bulan used for list period
                'tanggal_mulai' => $request->tanggal_mulai_rutin,
                'tanggal_selesai' => $request->tanggal_selesai_rutin,
                // bulan_mulai, bulan_selesai, frekuensi_pelaporan already in request
            ]);
        } else {
            // Skenario Insidentil
            $request->merge([
                // bulan already in request
                'tanggal_mulai' => $request->tanggal_mulai_insidentil,
                'tanggal_selesai' => $request->tanggal_selesai_insidentil,
                'frekuensi_pelaporan' => null,
                'bulan_mulai' => null,
                'bulan_selesai' => null,
            ]);
        }
        // ------------------------------------------------

        // Use TransactionService to store the Kegiatan
        $result = $this->transactionService->store($request, new Kegiatan(), $validationRules);

        $this->logActivityService->log('Stored new Kegiatan', 'Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Display the details of a specific Kegiatan by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $kegiatan = Kegiatan::getRelationship($id);
        if (!$kegiatan) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $pertanyaan = PertanyaanKegiatan::getByKegiatan($id);

        $data = [
            'kegiatan' => $kegiatan,
            'pertanyaan' => $pertanyaan
        ];

        $this->logActivityService->log('Viewed Kegiatan detail', 'ID: ' . $id);
        return $this->responseService->success($data, 'Data retrieved successfully');
    }

    /**
     * Update an existing Kegiatan record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $kegiatan = Kegiatan::find($id);

        if (!$kegiatan) {
            $this->logActivityService->log('Kegiatan not found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Validasi
        $validationRules = [
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id_tahun_anggaran',
            'jenis_kegiatan_id' => 'required|exists:jenis_kegiatan,id_jenis_kegiatan',
            'kode_kegiatan' => 'required|string|max:20',
            'nama_kegiatan' => 'required|string|max:500',

            // Logic Validasi Baru
            'frekuensi_pelaporan' => 'nullable|integer|in:1,2,3,4,6,12', // Jika ada, berarti Rutin

            // Insidentil
            'bulan' => 'nullable|required_without:frekuensi_pelaporan|integer|min:1|max:12',
            'tanggal_mulai_insidentil' => 'nullable|required_without:frekuensi_pelaporan|integer|min:1|max:31',
            'tanggal_selesai_insidentil' => 'nullable|required_without:frekuensi_pelaporan|integer|min:1|max:31',

            // Rutin
            'bulan_mulai' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:12',
            'bulan_selesai' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:12|gte:bulan_mulai',
            'tanggal_mulai_rutin' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:31',
            'tanggal_selesai_rutin' => 'nullable|required_with:frekuensi_pelaporan|integer|min:1|max:31',

            'batas_akhir_upload' => 'required|integer|min:1|max:31',
            'dasar_hukum' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        $validator->after(function ($validator) use ($request, $id) {
            if (!Kegiatan::isKodeUnique($request->kode_kegiatan, $request->tahun_anggaran_id, $id)) {
                $validator->errors()->add('kode_kegiatan', 'Kode kegiatan sudah digunakan untuk tahun anggaran ini.');
            }
        });

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Kegiatan update', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        // --- Data Mapping Logic (Rutin vs Insidentil) ---
        if ($request->filled('frekuensi_pelaporan')) {
            // Skenario Rutin
            $request->merge([
                'bulan' => $request->bulan_mulai, // Bulan used for list period
                'tanggal_mulai' => $request->tanggal_mulai_rutin,
                'tanggal_selesai' => $request->tanggal_selesai_rutin,
                // bulan_mulai, bulan_selesai, frekuensi_pelaporan already in request
            ]);
        } else {
            // Skenario Insidentil
            $request->merge([
                // bulan already in request
                'tanggal_mulai' => $request->tanggal_mulai_insidentil,
                'tanggal_selesai' => $request->tanggal_selesai_insidentil,
                'frekuensi_pelaporan' => null,
                'bulan_mulai' => null,
                'bulan_selesai' => null,
            ]);
        }
        // ------------------------------------------------

        // Use TransactionService to update the record
        $result = $this->transactionService->update($request, $kegiatan, $validationRules);

        $this->logActivityService->log('Updated Kegiatan', 'ID: ' . $id . ' Data: ' . json_encode($request->all()));

        return $result;
    }

    /**
     * Update status multiple an existing Kegiatan record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMultiple(Request $request)
    {
        $validationRules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:kegiatan,id_kegiatan',
            'status' => 'required|in:active,inactive',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during update status for multiple Kegiatan', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $selectedIds = $request->input('ids');
        $newStatus = $request->input('status');

        // Find the Kegiatan records by IDs
        $kegiatanRecords = Kegiatan::whereIn('id_kegiatan', $selectedIds)->get();

        if ($kegiatanRecords->isEmpty()) {
            $this->logActivityService->log('No Kegiatan records found for status update', 'IDs: ' . json_encode($selectedIds));
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        // Use TransactionService to update each record
        foreach ($kegiatanRecords as $kegiatan) {
            $request->merge(['status' => $newStatus]);
            $this->transactionService->update($request, $kegiatan, $validationRules);
        }

        $this->logActivityService->log('Updated status for multiple Kegiatan', 'IDs: ' . json_encode($selectedIds) . ' New Status: ' . $newStatus);

        return $this->responseService->success(null, 'Records updated successfully');
    }

    /**
     * Get kegiatan by tahun anggaran ID for dropdown.
     *
     * @param int $tahunAnggaranId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByTahunAnggaran($tahunAnggaranId)
    {
        $kegiatan = Kegiatan::where('tahun_anggaran_id', $tahunAnggaranId)
            ->where('status', 'active')
            ->orderBy('kode_kegiatan', 'ASC')
            ->get(['id_kegiatan', 'kode_kegiatan', 'nama_kegiatan']);

        $this->logActivityService->log('Fetched Kegiatan by Tahun Anggaran', 'Tahun Anggaran ID: ' . $tahunAnggaranId);
        return $this->responseService->success($kegiatan, 'Data retrieved successfully');
    }

    /**
     * Get kegiatan by jenis kegiatan ID for dropdown.
     *
     * @param int $jenisKegiatanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByJenisKegiatan($jenisKegiatanId)
    {
        $kegiatan = Kegiatan::where('jenis_kegiatan_id', $jenisKegiatanId)
            ->where('status', 'active')
            ->orderBy('kode_kegiatan', 'ASC')
            ->get(['id_kegiatan', 'kode_kegiatan', 'nama_kegiatan']);

        $this->logActivityService->log('Fetched Kegiatan by Jenis Kegiatan', 'Jenis Kegiatan ID: ' . $jenisKegiatanId);
        return $this->responseService->success($kegiatan, 'Data retrieved successfully');
    }
}
