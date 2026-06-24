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
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Harap selesaikan verifikasi reCAPTCHA.'
        ]);

        // Verifikasi reCAPTCHA
        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($response->json()['success'] ?? false)) {
            \Illuminate\Support\Facades\Log::error('reCAPTCHA failed', ['response' => $response->json()]);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'g-recaptcha-response' => 'Verifikasi reCAPTCHA gagal. Coba lagi.'
            ]);
        }

        $loginInput = $validated['login'];

        // 3. Ambil user berdasarkan email atau username
        $user = User::where(function ($query) use ($loginInput) {
            $query->where('email', $loginInput)
                  ->orWhere('username', $loginInput);
        })->first();

        // 4. Cek apakah user ditemukan dan aktif
        if ($user) {
            if ($user->status !== 'active') {
                return $this->responseService->error(
                    'Akun Anda tidak aktif. Silakan hubungi administrator.',
                    403
                );
            }

            // Keamanan Zombie Account: Tolak jika non-developer tapi data petugasnya kosong (atau terhapus)
            if (!$user->isDeveloper() && !$user->petugas) {
                return $this->responseService->error('Akun petugas Anda telah dihapus atau tidak valid.', 403);
            }

            // 5. Verifikasi password
            if (Hash::check($validated['password'], $user->password)) {
                RateLimiter::clear($throttleKey);

                // Cek Role Pengguna
                if ($user->isDeveloper()) {
                    Auth::login($user, $request->boolean('remember'));
                    $this->logActivityService->log("User {$user->username} logged in.");

                    return $this->responseService->success([
                        'redirect' => route('administrator.dashboard.index')
                    ], 'Login berhasil');
                } else {
                    // Petugas -> OTP Flow
                    $otpController = app(\App\Http\Controllers\Auth\OtpController::class);
                    $result = $otpController->requestOtp($user);

                    if ($result['status'] === 'error') {
                        return $this->responseService->error($result['message'], 429);
                    }

                    // Simpan ID user dan remember choice secara temporer
                    session([
                        'auth_user_id' => $user->id_user,
                        'auth_remember' => $request->boolean('remember')
                    ]);

                    return $this->responseService->success([
                        'redirect' => route('otp.verify')
                    ], 'Login valid. Meminta verifikasi OTP...');
                }
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
