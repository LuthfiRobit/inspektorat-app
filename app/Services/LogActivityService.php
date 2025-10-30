<?php

namespace App\Services;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LogActivityService
{
    /**
     * Menyimpan log aktivitas ke database
     *
     * @param string $action - Jenis aktivitas (Create, Update, Delete, Login, Logout, dll)
     * @param string|null $description - Deskripsi tambahan
     */
    public static function log($description = null)
    {
        LogActivity::create([
            'user_id'     => Auth::check() ? Auth::user()->id_user : 0,  // Cek apakah ada pengguna yang diautentikasi
            'action'      => request()->getMethod(),
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->header('user-agent')
        ]);
    }
}
