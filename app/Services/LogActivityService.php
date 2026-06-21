<?php

namespace App\Services;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        try {
            $userAgent = request()->header('user-agent');
            // Batasi panjang string untuk menghindari exception "Data too long for column"
            $userAgent = Str::limit($userAgent, 250, '');

            LogActivity::create([
                'user_id'     => Auth::check() ? Auth::user()->id_user : 0,  // Cek apakah ada pengguna yang diautentikasi
                'action'      => request()->getMethod(),
                'description' => $description,
                'ip_address'  => request()->ip(),
                'user_agent'  => $userAgent
            ]);
        } catch (\Exception $e) {
            // Log secara internal, namun tidak sampai melempar error (silent fail)
            Log::error('LogActivity Service Error: ' . $e->getMessage());
        }
    }
}
