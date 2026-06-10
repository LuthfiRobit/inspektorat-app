<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use App\Services\UserAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PetugasDesaController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $userAccountService;
    protected $scope = 'desa';

    public function __construct(ResponseService $responseService, TransactionService $transactionService, LogActivityService $logActivityService, UserAccountService $userAccountService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->userAccountService = $userAccountService;
    }
    /**
     * Display the index view for Petugas Desa.
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Petugas Desa');
        return view('administration.masters.petugas.desa.index');
    }

    /**
     * Retrieve and return the list of Petugas Desa for DataTables.
     */
    public function list(Request $request)
    {
        $filters = [
            'filter_status' => $request->input('filter_status', ''),
            'filter_kecamatan' => $request->input('filter_kecamatan', ''),
            'filter_desa' => $request->input('filter_desa', ''),
            'filter_jabatan' => $request->input('filter_jabatan', ''),
            'search' => $request->input('search', ''),
        ];

        $query = Petugas::getFilters($filters, $this->scope);

        $this->logActivityService->log('Fetched Petugas list', 'Filter: ' . json_encode($filters));

        return DataTables::of($query)
            ->addColumn(
                'checkbox',
                fn($row) =>
                '<input type="checkbox" class="table-checkbox form-check-input" name="petugas_ids[]"   id="checkbox_' . $row->id_petugas . '"  value="' . $row->id_petugas . '">'
            )
            ->addColumn('aksi', function ($item) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $hasShow = $user->hasPermissionTo('master.petugas.desa.view');
                $hasEdit = $user->hasPermissionTo('master.petugas.desa.edit');

                if (!$hasShow && !$hasEdit) {
                    return '<span class="text-muted">-</span>';
                }

                $btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $btn .= '<i class="fas fa-cogs"></i> Aksi';
                $btn .= '</button>';
                $btn .= '<div class="dropdown-menu">';

                if ($hasShow) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_show" data-id="' . $item->id_petugas . '">';
                    $btn .= '<i class="fas fa-eye"></i> Lihat';
                    $btn .= '</a>';
                }

                if ($hasEdit) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_edit" data-id="' . $item->id_petugas . '">';
                    $btn .= '<i class="fas fa-edit"></i> Edit';
                    $btn .= '</a>';

                    $btn .= '<a class="dropdown-item" href="javascript:void(0);" data-action="action_reset_password" data-id="' . $item->id_petugas . '">';
                    $btn .= '<i class="fas fa-key"></i> Reset Password';
                    $btn .= '</a>';
                }

                $btn .= '</div></div>';
                return $btn;
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
            ->editColumn('user_status', function ($row) {
                // badge untuk user status (akses login)
                $map = [
                    'active' => ['class' => 'light badge-success', 'label' => 'AKTIF'],
                    'inactive' => ['class' => 'light badge-danger', 'label' => 'NONAKTIF'],
                ];
                $badge = $map[$row->user_status] ?? ['class' => 'light badge-dark', 'label' => strtoupper($row->user_status ?? '-')];

                return '<span class="badge ' . $badge['class'] . '">' . $badge['label'] . '</span>';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'active' => ['class' => 'light badge-success', 'label' => 'AKTIF'],
                    'inactive' => ['class' => 'light badge-danger', 'label' => 'NONAKTIF'],
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
                'user_status',
                'status'
            ])
            ->make(true);
    }

    /**
     * Display the create view for Petugas Desa.
     */
    public function create()
    {
        $this->logActivityService->log('Accessed the create view for Petugas Desa');
        return view('administration.masters.petugas.desa.create');
    }

    /**
     * Store a new Petugas Desa record in the database.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kecamatan'],
            'nama_lengkap' => 'required|string|max:100',
            'nip' => 'required|string|max:20|unique:petugas,nip', // Ubah jadi required
            'jabatan' => 'required|string|max:100',
            'unit_kerja' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'required|email|max:100|unique:users,email', // Ubah jadi required dan unique di users table
            'alamat' => 'nullable|string',
            'tanggal_awal' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Petugas Desastore', 'Errors: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors());
        }

        $fileFields = [];
        $oldFiles = [];

        if ($request->hasFile('foto_petugas')) {
            $fileFields = [
                'foto_petugas' => 'petugas/foto',
            ];
        }

        $this->logActivityService->log('Stored new Petugas Desa', 'Data: ' . json_encode($request->all()));

        return $this->transactionService->store(
            $request,
            new Petugas(),
            $validationRules,
            function ($request, $petugas) {
                // Untuk Kecamatan, desa_id harus null
                $petugas->kecamatan_id = $request->kecamatan_id;
                $petugas->desa_id = $request->desa_id;
                // Create user account - gunakan input 'akses_login' untuk status user
                $userStatus = $request->input('akses_login', 'active');
                $user = $this->userAccountService->createPetugasAccount($petugas, $request->jabatan, $request->email, $userStatus);
                $petugas->user_id = $user->id_user;
                $petugas->save(); // Simpan 
            },
            $fileFields,
            $oldFiles
        );
    }

    /**
     * Display the details of a specific Petugas Desa by ID.
     */
    public function show($id)
    {
        $petugas = Petugas::getRelationship($id);

        if (!$petugas) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        $this->logActivityService->log('Viewed Petugas Desa detail', 'ID: ' . $id);
        return $this->responseService->success($petugas);
    }

    /**
     * Display the edit view for Petugas Desa.
     */
    public function edit($id)
    {
        $this->logActivityService->log('Accessed the edit view for Petugas Desa');
        return view('administration.masters.petugas.desa.edit');
    }

    /**
     * Update an existing Petugas Desa record.
     */
    public function update(Request $request, $id)
    {
        $petugas = Petugas::whereNotNull('desa_id')
            ->find($id);

        if (!$petugas) {
            $this->logActivityService->log('Petugas Desanot found for update', 'ID: ' . $id);
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }
        $validationRules = [
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kecamatan'],
            'nama_lengkap' => 'required|string|max:100',
            'nip' => 'required|string|max:20|unique:petugas,nip,' . $id . ',id_petugas',
            'jabatan' => 'required|string|max:100',
            'unit_kerja' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'required|email|max:100|unique:users,email,' . $petugas->user_id . ',id_user',
            'alamat' => 'nullable|string',
            'tanggal_awal' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log(
                'Validation failed during Petugas Desaupdate',
                'Errors: ' . json_encode($validator->errors())
            );
            return $this->responseService->validationError($validator->errors());
        }

        // 🔹 Siapkan file-field jika ada upload baru
        $fileFields = [];
        $oldFiles = [];

        if ($request->hasFile('foto_petugas')) {
            $fileFields = [
                'foto_petugas' => 'petugas/foto',
            ];

            if ($petugas->foto_petugas) {
                $oldFiles = [
                    'foto_petugas' => $petugas->foto_petugas,
                ];
            }
        }

        // 🔹 Jalankan transaksi update
        return $this->transactionService->update(
            $request,
            $petugas,
            $validationRules,
            function ($request, $petugas) {
                // Update akun user berdasarkan jabatan & status (akses login)
                $userStatus = $request->input('akses_login', 'active');
                $this->userAccountService->updatePetugasAccount(
                    $petugas,
                    $request->jabatan,
                    $request->email,
                    $userStatus
                );
            },
            $fileFields,
            $oldFiles,
            function () use ($id, $request) {
                // Logging hanya setelah transaksi berhasil
                $this->logActivityService->log(
                    'Updated Petugas Desa',
                    'ID: ' . $id . ' Data: ' . json_encode($request->except(['foto_petugas']))
                );
            }
        );
    }

    /**
     * Get desa by kecamatan (AJAX)
     */
    public function getDesaByKecamatan(Request $request)
    {
        $kecamatanId = $request->input('kecamatan_id');
        $desas = Desa::where('kecamatan_id', $kecamatanId)
            ->where('status', 'active')
            ->get();

        return $this->responseService->success($desas);
    }

    /**
     * Reset password and username to NIP
     */
    public function resetPassword($id)
    {
        $petugas = Petugas::find($id);

        if (!$petugas) {
            return $this->responseService->error('Data not found', ResponseService::STATUS_NOT_FOUND);
        }

        if (!$petugas->user_id) {
            return $this->responseService->error('Petugas tidak memiliki akun login', 400);
        }

        $user = \App\Models\User::find($petugas->user_id);
        if (!$user) {
            return $this->responseService->error('Akun login tidak ditemukan', ResponseService::STATUS_NOT_FOUND);
        }

        $temporaryPassword = bin2hex(random_bytes(4));

        $user->update([
            'username' => $petugas->nip,
            'password' => \Illuminate\Support\Facades\Hash::make($temporaryPassword),
        ]);

        $this->logActivityService->log('Reset Password Petugas Desa', 'ID: ' . $id . ' NIP: ' . $petugas->nip);

        return $this->responseService->success(
            ['temporary_password' => $temporaryPassword], 
            'Password berhasil direset.'
        );
    }
}
