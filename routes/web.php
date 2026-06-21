<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Masters\PertanyaanKegiatanController;
use App\Http\Controllers\Masters\DesaController;
use App\Http\Controllers\Masters\JenisKegiatanController;
use App\Http\Controllers\Masters\KecamatanController;
use App\Http\Controllers\Masters\KegiatanController;
use App\Http\Controllers\Masters\PersyaratanController;
use App\Http\Controllers\Masters\PetugasController;
use App\Http\Controllers\Masters\PetugasDesaController;
use App\Http\Controllers\Masters\PetugasInspektoratController;
use App\Http\Controllers\Masters\PetugasKecamatanController;
use App\Http\Controllers\Masters\TahunAnggaranController;
use App\Http\Controllers\Masters\WilayahBinaanController;
use App\Http\Controllers\Monev\LaporanHistoryController;
use App\Http\Controllers\Monev\LaporanKegiatanController;
use App\Http\Controllers\Monev\LaporanReviewController;
use App\Http\Controllers\Monitor\RiwayatLaporanController;
use App\Http\Controllers\Monitor\ScoringDesaController;
use App\Http\Controllers\Monitor\ScoringKecamatanController;
use App\Http\Controllers\Monitor\TarikDataLaporanController;
use App\Http\Controllers\System\ProfilController;
use App\Http\Controllers\Rbac\PermissionController;
use App\Http\Controllers\Rbac\RoleController;
use App\Http\Controllers\Rbac\UserController;
use App\Http\Controllers\System\LogActivityController;
use App\Http\Controllers\System\PermissionSyncController;
use App\Http\Controllers\System\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Notifications\TrialNotification;
Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('administrator.dashboard.index');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginView'])->name('login.view');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    // OTP Routes
    Route::get('otp/verify', [OtpController::class, 'verifyOtpView'])->name('otp.verify');
    Route::post('otp/verify', [OtpController::class, 'verifyOtp'])->name('otp.verify.post');
    Route::post('otp/resend', [OtpController::class, 'resendOtp'])->name('otp.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Route::get('dashboard', fn() => view('administration.dashboard.index'))->name('dashboard.index');

Route::middleware(['auth'])->prefix('administrator')->name('administrator.')->group(function () {

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index')->middleware('permission:dashboard.view');
        Route::get('/summary', [DashboardController::class, 'getSummary'])->name('summary')->middleware('permission:dashboard.view');
        Route::get('/recent-laporan/{type}', [DashboardController::class, 'getRecentLaporan'])->name('recent-laporan')->middleware('permission:dashboard.view');
        Route::get('/upcomming-kegiatan', [DashboardController::class, 'getUpcommingKegiatan'])->name('upcomming-kegiatan')->middleware('permission:dashboard.view');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfilController::class, 'index'])->name('index')->middleware('permission:profile.view');
        Route::put('/update', [ProfilController::class, 'update'])->name('update')->middleware('permission:profile.edit');
    });

    // Master
    Route::prefix('master')->name('master.')->group(function () {

        Route::prefix('kecamatan')->name('kecamatan.')->group(function () {
            Route::get('/', [KecamatanController::class, 'index'])->name('index')->middleware('permission:master.kecamatan.view');
            Route::get('/list', [KecamatanController::class, 'list'])->name('list')->middleware('permission:master.kecamatan.view');
            Route::post('/store', [KecamatanController::class, 'store'])->name('store')->middleware('permission:master.kecamatan.create');
            Route::get('/show/{id}', [KecamatanController::class, 'show'])->name('show')->middleware('permission:master.kecamatan.view');
            Route::put('/update/{id}', [KecamatanController::class, 'update'])->name('update')->middleware('permission:master.kecamatan.edit');
            Route::post('/update-status-multiple', [KecamatanController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.kecamatan.edit');
        });

        Route::prefix('desa')->name('desa.')->group(function () {
            Route::get('/', [DesaController::class, 'index'])->name('index')->middleware('permission:master.desa.view');
            Route::get('/list', [DesaController::class, 'list'])->name('list')->middleware('permission:master.desa.view');
            Route::get('/list-by-kecamatan/{id}', [DesaController::class, 'getByKecamatan'])->name('list-by-kecamatan')->middleware('permission:master.desa.json');
            Route::post('/store', [DesaController::class, 'store'])->name('store')->middleware('permission:master.desa.create');
            Route::get('/show/{id}', [DesaController::class, 'show'])->name('show')->middleware('permission:master.desa.view');
            Route::put('/update/{id}', [DesaController::class, 'update'])->name('update')->middleware('permission:master.desa.edit');
            Route::post('/update-status-multiple', [DesaController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.desa.edit');
        });

        Route::prefix('tahun-anggaran')->name('tahun-anggaran.')->group(function () {
            Route::get('/', [TahunAnggaranController::class, 'index'])->name('index')->middleware('permission:master.tahun-anggaran.view');
            Route::get('/list', [TahunAnggaranController::class, 'list'])->name('list')->middleware('permission:master.tahun-anggaran.view');
            Route::post('/store', [TahunAnggaranController::class, 'store'])->name('store')->middleware('permission:master.tahun-anggaran.create');
            Route::get('/show/{id}', [TahunAnggaranController::class, 'show'])->name('show')->middleware('permission:master.tahun-anggaran.view');
            Route::put('/update/{id}', [TahunAnggaranController::class, 'update'])->name('update')->middleware('permission:master.tahun-anggaran.edit');
            Route::post('/update-status-multiple', [TahunAnggaranController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.tahun-anggaran.edit');
        });

        Route::prefix('jenis-kegiatan')->name('jenis-kegiatan.')->group(function () {
            Route::get('/', [JenisKegiatanController::class, 'index'])->name('index')->middleware('permission:master.jenis-kegiatan.view');
            Route::get('/list', [JenisKegiatanController::class, 'list'])->name('list')->middleware('permission:master.jenis-kegiatan.view');
            Route::get('/list-by-tahun/{id}', [JenisKegiatanController::class, 'getByTahunAnggaran'])->name('list-by-tahun')->middleware('permission:master.jenis-kegiatan.json');
            Route::post('/store', [JenisKegiatanController::class, 'store'])->name('store')->middleware('permission:master.jenis-kegiatan.create');
            Route::get('/show/{id}', [JenisKegiatanController::class, 'show'])->name('show')->middleware('permission:master.jenis-kegiatan.view');
            Route::put('/update/{id}', [JenisKegiatanController::class, 'update'])->name('update')->middleware('permission:master.jenis-kegiatan.edit');
            Route::post('/update-status-multiple', [JenisKegiatanController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.jenis-kegiatan.edit');
        });

        Route::prefix('kegiatan')->name('kegiatan.')->group(function () {
            Route::get('/', [KegiatanController::class, 'index'])->name('index')->middleware('permission:master.kegiatan.view');
            Route::get('/list', [KegiatanController::class, 'list'])->name('list')->middleware('permission:master.kegiatan.view');
            Route::post('/store', [KegiatanController::class, 'store'])->name('store')->middleware('permission:master.kegiatan.create');
            Route::get('/show/{id}', [KegiatanController::class, 'show'])->name('show')->middleware('permission:master.kegiatan.view');
            Route::put('/update/{id}', [KegiatanController::class, 'update'])->name('update')->middleware('permission:master.kegiatan.edit');
            Route::post('/update-status-multiple', [KegiatanController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.kegiatan.edit');
        });

        Route::prefix('pertanyaan-kegiatan')->name('pertanyaan-kegiatan.')->group(function () {
            Route::get('/', [PertanyaanKegiatanController::class, 'index'])->name('index')->middleware('permission:master.pertanyaan-kegiatan.view');
            Route::get('/list', [PertanyaanKegiatanController::class, 'list'])->name('list')->middleware('permission:master.pertanyaan-kegiatan.view');
            Route::get('/list-by-kegiatan/{id}', [PertanyaanKegiatanController::class, 'getByKegiatan'])->name('list-by-kegiatan')->middleware('permission:master.pertanyaan-kegiatan.json');
            Route::post('/store', [PertanyaanKegiatanController::class, 'store'])->name('store')->middleware('permission:master.pertanyaan-kegiatan.create');
            Route::get('/show/{id}', [PertanyaanKegiatanController::class, 'show'])->name('show')->middleware('permission:master.pertanyaan-kegiatan.view');
            Route::put('/update/{id}', [PertanyaanKegiatanController::class, 'update'])->name('update')->middleware('permission:master.pertanyaan-kegiatan.edit');
            Route::post('/update-status-multiple', [PertanyaanKegiatanController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.pertanyaan-kegiatan.edit');
        });

        Route::prefix('persyaratan')->name('persyaratan.')->group(function () {
            Route::get('/', [PersyaratanController::class, 'index'])->name('index')->middleware('permission:master.persyaratan.view');
            Route::get('/list', [PersyaratanController::class, 'list'])->name('list')->middleware('permission:master.persyaratan.view');
            Route::post('/store', [PersyaratanController::class, 'store'])->name('store')->middleware('permission:master.persyaratan.create');
            Route::get('/show/{id}', [PersyaratanController::class, 'show'])->name('show')->middleware('permission:master.persyaratan.view');
            Route::put('/update/{id}', [PersyaratanController::class, 'update'])->name('update')->middleware('permission:master.persyaratan.edit');
            Route::post('/update-status-multiple', [PersyaratanController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:master.persyaratan.edit');
        });

        Route::prefix('petugas')->name('petugas.')->group(function () {
            Route::prefix('inspektorat')->name('inspektorat.')->group(function () {
                Route::get('/', [PetugasInspektoratController::class, 'index'])->name('index')->middleware('permission:master.petugas.inspektorat.view');
                Route::get('/list', [PetugasInspektoratController::class, 'list'])->name('list')->middleware('permission:master.petugas.inspektorat.view');
                Route::get('/create', [PetugasInspektoratController::class, 'create'])->name('create')->middleware('permission:master.petugas.inspektorat.create');
                Route::post('/store', [PetugasInspektoratController::class, 'store'])->name('store')->middleware('permission:master.petugas.inspektorat.create');
                Route::get('/show/{id}', [PetugasInspektoratController::class, 'show'])->name('show')->middleware('permission:master.petugas.inspektorat.view');
                Route::get('/edit/{id}', [PetugasInspektoratController::class, 'edit'])->name('edit')->middleware('permission:master.petugas.inspektorat.edit');
                Route::put('/update/{id}', [PetugasInspektoratController::class, 'update'])->name('update')->middleware('permission:master.petugas.inspektorat.edit');
                Route::delete('/{id}', [PetugasInspektoratController::class, 'destroy'])->name('destroy')->middleware('permission:master.petugas.inspektorat.delete');
                Route::post('/reset-password/{id}', [PetugasInspektoratController::class, 'resetPassword'])->name('reset-password')->middleware('permission:master.petugas.inspektorat.edit');
            });

            Route::prefix('kecamatan')->name('kecamatan.')->group(function () {
                Route::get('/', [PetugasKecamatanController::class, 'index'])->name('index')->middleware('permission:master.petugas.kecamatan.view');
                Route::get('/list', [PetugasKecamatanController::class, 'list'])->name('list')->middleware('permission:master.petugas.kecamatan.view');
                Route::get('/create', [PetugasKecamatanController::class, 'create'])->name('create')->middleware('permission:master.petugas.kecamatan.create');
                Route::post('/store', [PetugasKecamatanController::class, 'store'])->name('store')->middleware('permission:master.petugas.kecamatan.create');
                Route::get('/show/{id}', [PetugasKecamatanController::class, 'show'])->name('show')->middleware('permission:master.petugas.kecamatan.view');
                Route::get('/edit/{id}', [PetugasKecamatanController::class, 'edit'])->name('edit')->middleware('permission:master.petugas.kecamatan.edit');
                Route::put('/update/{id}', [PetugasKecamatanController::class, 'update'])->name('update')->middleware('permission:master.petugas.kecamatan.edit');
                Route::delete('/{id}', [PetugasKecamatanController::class, 'destroy'])->name('destroy')->middleware('permission:master.petugas.kecamatan.delete');
                Route::post('/reset-password/{id}', [PetugasKecamatanController::class, 'resetPassword'])->name('reset-password')->middleware('permission:master.petugas.kecamatan.edit');
            });

            Route::prefix('desa')->name('desa.')->group(function () {
                Route::get('/', [PetugasDesaController::class, 'index'])->name('index')->middleware('permission:master.petugas.desa.view');
                Route::get('/list', [PetugasDesaController::class, 'list'])->name('list')->middleware('permission:master.petugas.desa.view');
                Route::get('/create', [PetugasDesaController::class, 'create'])->name('create')->middleware('permission:master.petugas.desa.create');
                Route::post('/store', [PetugasDesaController::class, 'store'])->name('store')->middleware('permission:master.petugas.desa.create');
                Route::get('/show/{id}', [PetugasDesaController::class, 'show'])->name('show')->middleware('permission:master.petugas.desa.view');
                Route::get('/edit/{id}', [PetugasDesaController::class, 'edit'])->name('edit')->middleware('permission:master.petugas.desa.edit');
                Route::put('/update/{id}', [PetugasDesaController::class, 'update'])->name('update')->middleware('permission:master.petugas.desa.edit');
                Route::delete('/{id}', [PetugasDesaController::class, 'destroy'])->name('destroy')->middleware('permission:master.petugas.desa.delete');
                Route::post('/reset-password/{id}', [PetugasDesaController::class, 'resetPassword'])->name('reset-password')->middleware('permission:master.petugas.desa.edit');
            });
        });

        Route::prefix('wilayah-binaan')->name('wilayah-binaan.')->group(function () {
            Route::get('/', [WilayahBinaanController::class, 'index'])->name('index')->middleware('permission:master.wilayah-binaan.view');
            Route::get('/list-inspektorat', [WilayahBinaanController::class, 'listInspektorat'])->name('list-inspektorat')->middleware('permission:master.wilayah-binaan.view');
            Route::get('/list-kecamatan', [WilayahBinaanController::class, 'listKecamatan'])->name('list-kecamatan')->middleware('permission:master.wilayah-binaan.view');
            Route::get('/{id}', [WilayahBinaanController::class, 'show'])->name('show')->middleware('permission:master.wilayah-binaan.view');
            Route::get('/{id}/data', [WilayahBinaanController::class, 'getAssignedAndAvailable'])->name('data')->middleware('permission:master.wilayah-binaan.view');
            Route::post('/bulk', [WilayahBinaanController::class, 'bulkStore'])->name('bulk')->middleware('permission:master.wilayah-binaan.create');
            Route::delete('/{id}', [WilayahBinaanController::class, 'destroy'])->name('destroy')->middleware('permission:master.wilayah-binaan.delete');
        });
    });

    // Monev
    Route::prefix('monev')->name('monev.')->group(function () {

        Route::prefix('laporan')->name('laporan.')->group(function () {
            // READ / VIEW
            Route::get('/', [LaporanKegiatanController::class, 'index'])->name('index')->middleware('permission:monev.laporan.view');
            Route::get('/list', [LaporanKegiatanController::class, 'list'])->name('list')->middleware('permission:monev.laporan.view');
            Route::get('/show/{id}', [LaporanKegiatanController::class, 'show'])->name('show')->middleware('permission:monev.laporan.view');
            Route::get('/show-request', [LaporanKegiatanController::class, 'showRequest'])->name('show-request')->middleware('permission:monev.laporan.view');

            // CREATE
            Route::get('/create', [LaporanKegiatanController::class, 'create'])->name('create')->middleware('permission:monev.laporan.create');
            Route::post('/store', [LaporanKegiatanController::class, 'store'])->name('store')->middleware('permission:monev.laporan.create');

            // EDIT / UPDATE
            Route::get('/edit', [LaporanKegiatanController::class, 'edit'])->name('edit')->middleware('permission:monev.laporan.edit');
            Route::put('/update/{id}', [LaporanKegiatanController::class, 'update'])->name('update')->middleware('permission:monev.laporan.edit');

            // DATA FETCHING (AJAX/API)
            Route::get('/get-data/{id}', [LaporanKegiatanController::class, 'getData'])->name('get-data')->middleware('permission:monev.laporan.json');
            Route::get('/get-kegiatan-data', [LaporanKegiatanController::class, 'getKegiatanData'])->name('get-kegiatan-data')->middleware('permission:monev.laporan.json');
        });

        Route::prefix('review')->name('review.')->group(function () {
            Route::get('/', [LaporanReviewController::class, 'index'])->name('index')->middleware('permission:monev.review.view');
            Route::get('/list', [LaporanReviewController::class, 'list'])->name('list')->middleware('permission:monev.review.view');
            Route::get('/review', [LaporanReviewController::class, 'review'])->name('review')->middleware('permission:monev.review.edit');
            Route::post('/submit', [LaporanReviewController::class, 'submit'])->name('submit')->middleware('permission:monev.review.create');
        });
    });

    // Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::prefix('riwayat')->name('riwayat.')->group(function () {
            Route::get('/', [RiwayatLaporanController::class, 'index'])->name('index')->middleware('permission:monitoring.riwayat.view');
            Route::get('/list', [RiwayatLaporanController::class, 'list'])->name('list')->middleware('permission:monitoring.riwayat.view');
            Route::get('/detail', [RiwayatLaporanController::class, 'detail'])->name('detail')->middleware('permission:monitoring.riwayat.view');
            Route::get('/get-data/{id}', [RiwayatLaporanController::class, 'getData'])->name('get-data')->middleware('permission:monitoring.riwayat.json');
        });

        Route::prefix('scoring')->name('scoring.')->group(function () {
            Route::prefix('desa')->name('desa.')->group(function () {
                Route::get('/', [ScoringDesaController::class, 'index'])->name('index')->middleware('permission:monitoring.scoring.desa.view');
                Route::get('/list', [ScoringDesaController::class, 'list'])->name('list')->middleware('permission:monitoring.scoring.desa.view');
                Route::get('/detail/{id}', [ScoringDesaController::class, 'detail'])->name('detail')->middleware('permission:monitoring.scoring.desa.view');
            });

            Route::prefix('kecamatan')->name('kecamatan.')->group(function () {
                Route::get('/', [ScoringKecamatanController::class, 'index'])->name('index')->middleware('permission:monitoring.scoring.kecamatan.view');
                Route::get('/list', [ScoringKecamatanController::class, 'list'])->name('list')->middleware('permission:monitoring.scoring.kecamatan.view');
                Route::get('/detail/{id}', [ScoringKecamatanController::class, 'detail'])->name('detail')->middleware('permission:monitoring.scoring.kecamatan.view');
            });
        });

        Route::prefix('tarik-data')->name('tarik-data.')->group(function () {
            Route::get('/', [TarikDataLaporanController::class, 'index'])->name('index')->middleware('permission:monitoring.tarik-data.view');
            Route::get('/list', [TarikDataLaporanController::class, 'list'])->name('list')->middleware('permission:monitoring.tarik-data.view');
            Route::get('/export', [TarikDataLaporanController::class, 'export'])->name('export')->middleware('permission:monitoring.tarik-data.export');
        });
    });

    // System
    Route::prefix('system')->name('system.')->group(function () {

        Route::prefix('log-activity')->name('log-activity.')->group(function () {
            Route::get('/', [LogActivityController::class, 'index'])->name('index')->middleware('permission:system.log-activity.view');
            Route::get('/list', [LogActivityController::class, 'list'])->name('list')->middleware('permission:system.log-activity.view');
            Route::delete('/clear', [LogActivityController::class, 'clear'])->name('clear')->middleware('permission:system.log-activity.delete');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/read/{id}', [NotificationController::class, 'markAsReadAndRedirect'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        });
    });

    // RBAC
    Route::prefix('rbac')->name('rbac.')->group(function () {

        Route::prefix('role')->name('role.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:rbac.role.view');
            Route::get('/list', [RoleController::class, 'list'])->name('list')->middleware('permission:rbac.role.view');
            Route::post('/store', [RoleController::class, 'store'])->name('store')->middleware('permission:rbac.role.create');
            Route::get('/show/{id}', [RoleController::class, 'show'])->name('show')->middleware('permission:rbac.role.view');
            Route::put('/update/{id}', [RoleController::class, 'update'])->name('update')->middleware('permission:rbac.role.edit');
            Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('edit')->middleware('permission:rbac.role.edit');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:rbac.role.delete');
            Route::get('/list-role-permission/{id}', [RoleController::class, 'listRolePermission'])->name('list-role-permission')->middleware('permission:rbac.role.view');
            Route::post('/store-role-permission/{id}', [RoleController::class, 'storeRolePermission'])->name('store-role-permission')->middleware('permission:rbac.role.edit');
        });

        Route::prefix('permission')->name('permission.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index')->middleware('permission:rbac.permission.view');
            Route::get('/list', [PermissionController::class, 'list'])->name('list')->middleware('permission:rbac.permission.view');
            Route::post('/sync', [PermissionSyncController::class, 'sync'])->name('sync')->middleware(['throttle:1,1', 'permission:rbac.permission.view']);
            Route::post('/store', [PermissionController::class, 'store'])->name('store')->middleware('permission:rbac.permission.create');
            Route::get('/show/{id}', [PermissionController::class, 'show'])->name('show')->middleware('permission:rbac.permission.view');
            Route::put('/update/{id}', [PermissionController::class, 'update'])->name('update')->middleware('permission:rbac.permission.edit');
        });

        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:rbac.user.view');
            Route::get('/list', [UserController::class, 'list'])->name('list')->middleware('permission:rbac.user.view');
            Route::post('/store', [UserController::class, 'store'])->name('store')->middleware('permission:rbac.user.create');
            Route::get('/show/{id}', [UserController::class, 'show'])->name('show')->middleware('permission:rbac.user.view');
            Route::put('/update/{id}', [UserController::class, 'update'])->name('update')->middleware('permission:rbac.user.edit');
            Route::post('/update-status', [UserController::class, 'updateStatus'])->name('update-status')->middleware('permission:rbac.user.edit');
            Route::post('/update-status-multiple', [UserController::class, 'updateStatusMultiple'])->name('update-status-multiple')->middleware('permission:rbac.user.edit');
            Route::get('/list-user-role/{id}', [UserController::class, 'listUserRole'])->name('list-user-role')->middleware('permission:rbac.user.view');
            Route::post('/store-user-role/{id}', [UserController::class, 'storeUserRole'])->name('store-user-role')->middleware('permission:rbac.user.edit');
        });
    });
});


Route::get('/send-email',function(){
    $data = [
        'name' => 'Syahrizal As',
        'body' => 'Testing Kirim Email di Santri Koding'
    ];
   
    Mail::to('luthfilearndev@gmail.com')->send(new TrialNotification($data));
   
    dd("Email Berhasil dikirim.");
});