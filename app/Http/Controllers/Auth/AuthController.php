<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\LogActivityService;
use App\Services\ResponseService;

class AuthController extends Controller
{
    protected $logActivityService;
    protected $responseService;

    // Injecting services
    public function __construct(LogActivityService $logActivityService, ResponseService $responseService)
    {
        $this->logActivityService = $logActivityService;
        $this->responseService = $responseService;
    }

    public function loginView()
    {
        $this->logActivityService->log('Accessed the login view.'); // Log the view access
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $ip = $request->ip();
        $throttleKey = 'login:' . $ip;

        // 1. Rate Limiting
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $remainingSeconds = RateLimiter::availableIn($throttleKey);

            return $this->responseService->error(
                'Terlalu banyak percobaan login. Coba lagi dalam ' . $remainingSeconds . ' detik.',
                429
            );
        }

        // 2. Validasi input
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $validated['login'];

        // 3. Ambil user berdasarkan email atau username
        $user = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        // 4. Cek apakah user ditemukan dan aktif
        if ($user) {
            if ($user->status !== 'active') {
                return $this->responseService->error(
                    'Akun Anda tidak aktif. Silakan hubungi administrator.',
                    403
                );
            }

            // 5. Verifikasi password
            if (Hash::check($validated['password'], $user->password)) {
                Auth::login($user, $request->boolean('remember'));
                RateLimiter::clear($throttleKey);

                $this->logActivityService->log("User {$user->username} logged in.");

                return $this->responseService->success([
                    'redirect' => route('administrator.dashboard.index')
                ], 'Login berhasil');
            }
        }

        // 6. Login gagal
        RateLimiter::hit($throttleKey);
        $this->logActivityService->log("Gagal login untuk input: {$loginInput} dari IP: {$ip}");

        return $this->responseService->error('Kredensial tidak valid.', 401);
    }


    public function logout(Request $request)
    {
        $user = Auth::user(); // Get the currently logged in user before logout.
        if ($user) {
            $this->logActivityService->log("User {$user->email} logged out."); // Log before logout
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }
}
