<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Petugas;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserAccountService
{
    /**
     * Create a user and assign role for Petugas based on jabatan.
     */
    public function createPetugasAccount(object $petugas, string $jabatan, string $email, string $status = 'active'): User
    {
        return $this->createUserWithRole(
            name: $petugas->nama_lengkap,
            email: $email, // Langsung dari request->email
            status: $status,
            roleName: $jabatan, // Langsung gunakan jabatan sebagai role name
            username: $petugas->nip, // Username menggunakan NIP dari request
            password: $petugas->nip // Password menggunakan NIP dari request
        );
    }

    /**
     * Generic user creation + role assignment
     */
    protected function createUserWithRole(
        string $name,
        string $email, // Required, tidak nullable
        string $status,
        string $roleName,
        string $username, // Required, dari NIP
        string $password // Required, dari NIP
    ): User {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'username' => $username,
            'password' => Hash::make($password),
            'status'   => $status,
        ]);

        $roleId = Role::where('role_name', $roleName)->value('id_role');

        if (!$roleId) {
            // Fallback ke role 'petugas' jika role tidak ditemukan
            $roleId = Role::where('role_name', 'petugas')->value('id_role');

            if (!$roleId) {
                throw new Exception("Role '$roleName' tidak ditemukan dan fallback role 'petugas' juga tidak tersedia.");
            }
        }

        UserRole::create([
            'user_id' => $user->id_user,
            'role_id' => $roleId,
        ]);

        return $user;
    }

    /**
     * Update user account when petugas is updated
     */
    public function updatePetugasAccount(object $petugas, string $jabatan, string $email, string $status = 'active'): ?User
    {
        if (!$email) {
            throw new Exception("Role '$email' tidak ditemukan dan fallback role 'petugas' juga tidak tersedia.");
        }
        if (!$petugas->user_id) {
            return $this->createPetugasAccount($petugas, $jabatan, $status);
        }

        $user = User::find($petugas->user_id);
        if (!$user) {
            return $this->createPetugasAccount($petugas, $jabatan, $status);
        }

        $roleId = Role::where('role_name', $jabatan)->value('id_role');

        if (!$roleId) {
            // Fallback ke role 'petugas' jika role tidak ditemukan
            $roleId = Role::where('role_name', 'petugas')->value('id_role');

            if (!$roleId) {
                throw new Exception("Role '$jabatan' tidak ditemukan dan fallback role 'petugas' juga tidak tersedia.");
            }
        }

        // Update user data - TIDAK mengubah username dan password, hanya name, email, status
        $user->update([
            'name' => $petugas->nama_lengkap,
            'email' => $email, // Email dari request
            'status' => $status,
        ]);

        // Update user role jika jabatan berubah
        $userRole = UserRole::where('user_id', $user->id_user)->first();
        if ($userRole) {
            $userRole->update([
                'role_id' => $roleId
            ]);
        } else {
            // Buat user role jika tidak ada
            UserRole::create([
                'user_id' => $user->id_user,
                'role_id' => $roleId,
            ]);
        }

        return $user;
    }
}
