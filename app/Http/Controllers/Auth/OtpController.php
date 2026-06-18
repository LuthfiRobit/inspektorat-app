<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Mail\SendOtpMail;
use App\Services\ResponseService;
use App\Services\LogActivityService;

class OtpController extends Controller
{
    protected $responseService;
    protected $logActivityService;

    public function __construct(ResponseService $responseService, LogActivityService $logActivityService)
    {
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function verifyOtpView()
    {
        if (!session()->has('auth_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.otp');
    }

    public function requestOtp($user)
    {
        $throttleKey = 'otp_request:' . $user->email;

        // Rate limit: 1 request per 3 menit (180 detik)
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return ['status' => 'error', 'message' => "Tunggu {$minutes} menit ({$seconds} detik) sebelum meminta OTP baru."];
        }

        try {
            // 1. Generate Secure 6-digit OTP Cryptographically
            $otpRaw = random_int(100000, 999999);
            
            // 2. Hash the OTP (Protect against DB leak)
            $otpHashed = Hash::make($otpRaw);

            // 3. Store in database cache for exactly 3 minutes
            Cache::put('otp_' . $user->email, $otpHashed, now()->addMinutes(3));

            // 4. Send Email (Pass raw OTP to mailable)
            Mail::to($user->email)->send(new SendOtpMail($otpRaw, $user->name));

            // Hit rate limiter (Lock for 3 minutes)
            RateLimiter::hit($throttleKey, 180);

            return ['status' => 'success'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal mengirim OTP ke email: ' . $e->getMessage()];
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|numeric|digits:6'
        ]);

        $userId = session('auth_user_id');
        if (!$userId) {
            return $this->responseService->error('Sesi tidak valid, silakan login ulang dari awal.', 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->responseService->error('Pengguna tidak ditemukan.', 404);
        }

        // Fetch hashed OTP from Database Cache
        $hashedOtp = Cache::get('otp_' . $user->email);

        if (!$hashedOtp) {
            return $this->responseService->error('Kode OTP sudah kedaluwarsa atau belum dikirim.', 400);
        }

        // Verify the Raw Input against Hash
        if (Hash::check($request->otp_code, $hashedOtp)) {
            // 1. INVALIDATE OTP: Immediately destroy the cache
            Cache::forget('otp_' . $user->email);
            
            // 2. CLEAR RATE LIMIT: Reset for future logins
            RateLimiter::clear('otp_request:' . $user->email);

            // 3. AUTHENTICATE
            $remember = session('auth_remember', false);
            Auth::login($user, $remember);
            session()->forget(['auth_user_id', 'auth_remember']);

            $this->logActivityService->log("User {$user->email} logged in successfully via OTP.");

            return $this->responseService->success([
                'redirect' => route('administrator.dashboard.index')
            ], 'Verifikasi OTP berhasil. Memuat Dashboard...');
        }

        // Hash Check Failed
        $this->logActivityService->log("User {$user->email} failed OTP verification.");
        return $this->responseService->error('Kode OTP tidak valid.', 400);
    }

    public function resendOtp(Request $request)
    {
        $userId = session('auth_user_id');
        if (!$userId) {
            return $this->responseService->error('Sesi tidak valid, silakan login ulang.', 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->responseService->error('Pengguna tidak ditemukan.', 404);
        }

        // Route the resend request to the secure method
        $result = $this->requestOtp($user);

        if ($result['status'] === 'success') {
            return $this->responseService->success(null, 'Kode OTP yang baru telah dikirimkan ke email Anda.');
        }

        return $this->responseService->error($result['message'], 429); // 429 Too Many Requests
    }
}
