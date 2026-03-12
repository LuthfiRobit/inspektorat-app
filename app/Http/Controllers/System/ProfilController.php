<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\User;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfilController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    public function __construct(ResponseService $responseService, TransactionService $transactionService, LogActivityService $logActivityService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
    }

    /**
     * Display the profile index view.
     */
    public function index()
    {
        $user = Auth::user();
        $petugas = Petugas::where('user_id', $user->id_user)->first();

        // If user is not associated with any petugas, we just show standard user data,
        // but for this app context it assumes Petugas relation.
        
        $this->logActivityService->log('Accessed Profile view');
        return view('administration.profile.index', compact('user', 'petugas'));
    }

    /**
     * Update the profile and password.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $petugas = Petugas::where('user_id', $user->id_user)->first();

        if (!$petugas) {
            return $this->responseService->error('Data petugas tidak ditemukan.', 404);
        }

        $validationRules = [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id_user . ',id_user',
            'no_telp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'foto_petugas' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:8|confirmed', // Require confirmation
            'password_confirmation' => 'nullable|string|min:8',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $this->logActivityService->log('Validation failed during Profil update: ' . json_encode($validator->errors()));
            return $this->responseService->validationError($validator->errors()->toArray());
        }

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

        return $this->transactionService->update(
            $request,
            $petugas,
            $validationRules,
            function ($request, $petugas) use ($user) {
                // Update tabel User
                $userData = [
                    'name' => $petugas->nama_lengkap,
                    'email' => $request->email,
                ];

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $userModel = User::find($user->id_user);
                $userModel->update($userData);
                
                $this->logActivityService->log('Updated Profile for User ID: ' . $user->id_user);
            },
            $fileFields,
            $oldFiles
        );
    }
}
