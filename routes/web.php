<?php

use App\Http\Controllers\Auth\AuthController;
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
use App\Http\Controllers\Monev\LaporanHistoryController;
use App\Http\Controllers\Monev\LaporanKegiatanController;
use App\Http\Controllers\Monev\LaporanReviewController;
use App\Http\Controllers\Monitor\RiwayatLaporanController;
use App\Http\Controllers\Monitor\ScoringDesaController;
use App\Http\Controllers\Monitor\ScoringKecamatanController;
use App\Http\Controllers\Rbac\PermissionController;
use App\Http\Controllers\Rbac\RoleController;
use App\Http\Controllers\Rbac\UserController;
use App\Http\Controllers\System\LogActivityController;
use App\Http\Controllers\System\PermissionSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('administrator.dashboard.index');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginView'])->name('login.view');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Route::get('dashboard', fn() => view('administration.dashboard.index'))->name('dashboard.index');

Route::middleware(['auth', 'checkPermission'])->prefix('administrator')->name('administrator.')->group(function () {

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/summary', [DashboardController::class, 'getSummary'])->name('summary');
        Route::get('/recent-laporan/{type}', [DashboardController::class, 'getRecentLaporan'])->name('recent-laporan');
        Route::get('/upcomming-kegiatan', [DashboardController::class, 'getUpcommingKegiatan'])->name('upcomming-kegiatan');
    });

    // Master
    Route::prefix('master')->name('master.')->group(function () {

        Route::prefix('kecamatan')->name('kecamatan.')->group(function () {
            Route::get('/', [KecamatanController::class, 'index'])->name('index');
            Route::get('/list', [KecamatanController::class, 'list'])->name('list');
            Route::post('/store', [KecamatanController::class, 'store'])->name('store');
            Route::get('/show/{id}', [KecamatanController::class, 'show'])->name('show');
            Route::put('/update/{id}', [KecamatanController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [KecamatanController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('desa')->name('desa.')->group(function () {
            Route::get('/', [DesaController::class, 'index'])->name('index');
            Route::get('/list', [DesaController::class, 'list'])->name('list');
            Route::get('/list-by-kecamatan/{id}', [DesaController::class, 'getByKecamatan'])->name('list-by-kecamatan');
            Route::post('/store', [DesaController::class, 'store'])->name('store');
            Route::get('/show/{id}', [DesaController::class, 'show'])->name('show');
            Route::put('/update/{id}', [DesaController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [DesaController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('tahun-anggaran')->name('tahun-anggaran.')->group(function () {
            Route::get('/', [TahunAnggaranController::class, 'index'])->name('index');
            Route::get('/list', [TahunAnggaranController::class, 'list'])->name('list');
            Route::post('/store', [TahunAnggaranController::class, 'store'])->name('store');
            Route::get('/show/{id}', [TahunAnggaranController::class, 'show'])->name('show');
            Route::put('/update/{id}', [TahunAnggaranController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [TahunAnggaranController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('jenis-kegiatan')->name('jenis-kegiatan.')->group(function () {
            Route::get('/', [JenisKegiatanController::class, 'index'])->name('index');
            Route::get('/list', [JenisKegiatanController::class, 'list'])->name('list');
            Route::get('/list-by-tahun/{id}', [JenisKegiatanController::class, 'getByTahunAnggaran'])->name('list-by-tahun');
            Route::post('/store', [JenisKegiatanController::class, 'store'])->name('store');
            Route::get('/show/{id}', [JenisKegiatanController::class, 'show'])->name('show');
            Route::put('/update/{id}', [JenisKegiatanController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [JenisKegiatanController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('kegiatan')->name('kegiatan.')->group(function () {
            Route::get('/', [KegiatanController::class, 'index'])->name('index');
            Route::get('/list', [KegiatanController::class, 'list'])->name('list');
            Route::post('/store', [KegiatanController::class, 'store'])->name('store');
            Route::get('/show/{id}', [KegiatanController::class, 'show'])->name('show');
            Route::put('/update/{id}', [KegiatanController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [KegiatanController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('pertanyaan-kegiatan')->name('pertanyaan-kegiatan.')->group(function () {
            Route::get('/', [PertanyaanKegiatanController::class, 'index'])->name('index');
            Route::get('/list', [PertanyaanKegiatanController::class, 'list'])->name('list');
            Route::get('/list-by-kegiatan/{id}', [PertanyaanKegiatanController::class, 'getByKegiatan'])->name('list-by-kegiatan');
            Route::post('/store', [PertanyaanKegiatanController::class, 'store'])->name('store');
            Route::get('/show/{id}', [PertanyaanKegiatanController::class, 'show'])->name('show');
            Route::put('/update/{id}', [PertanyaanKegiatanController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [PertanyaanKegiatanController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('persyaratan')->name('persyaratan.')->group(function () {
            Route::get('/', [PersyaratanController::class, 'index'])->name('index');
            Route::get('/list', [PersyaratanController::class, 'list'])->name('list');
            Route::post('/store', [PersyaratanController::class, 'store'])->name('store');
            Route::get('/show/{id}', [PersyaratanController::class, 'show'])->name('show');
            Route::put('/update/{id}', [PersyaratanController::class, 'update'])->name('update');
            Route::post('/update-status-multiple', [PersyaratanController::class, 'updateStatusMultiple'])->name('update-status-multiple');
        });

        Route::prefix('petugas')->name('petugas.')->group(function () {
            Route::prefix('inspektorat')->name('inspektorat.')->group(function () {
                Route::get('/', [PetugasInspektoratController::class, 'index'])->name('index');
                Route::get('/list', [PetugasInspektoratController::class, 'list'])->name('list');
                Route::get('/create', [PetugasInspektoratController::class, 'create'])->name('create');
                Route::post('/store', [PetugasInspektoratController::class, 'store'])->name('store');
                Route::get('/show/{id}', [PetugasInspektoratController::class, 'show'])->name('show');
                Route::get('/edit/{id}', [PetugasInspektoratController::class, 'edit'])->name('edit');
                Route::put('/update/{id}', [PetugasInspektoratController::class, 'update'])->name('update');
            });

            Route::prefix('kecamatan')->name('kecamatan.')->group(function () {
                Route::get('/', [PetugasKecamatanController::class, 'index'])->name('index');
                Route::get('/list', [PetugasKecamatanController::class, 'list'])->name('list');
                Route::get('/create', [PetugasKecamatanController::class, 'create'])->name('create');
                Route::post('/store', [PetugasKecamatanController::class, 'store'])->name('store');
                Route::get('/show/{id}', [PetugasKecamatanController::class, 'show'])->name('show');
                Route::get('/edit/{id}', [PetugasKecamatanController::class, 'edit'])->name('edit');
                Route::put('/update/{id}', [PetugasKecamatanController::class, 'update'])->name('update');
            });

            Route::prefix('desa')->name('desa.')->group(function () {
                Route::get('/', [PetugasDesaController::class, 'index'])->name('index');
                Route::get('/list', [PetugasDesaController::class, 'list'])->name('list');
                Route::get('/create', [PetugasDesaController::class, 'create'])->name('create');
                Route::post('/store', [PetugasDesaController::class, 'store'])->name('store');
                Route::get('/show/{id}', [PetugasDesaController::class, 'show'])->name('show');
                Route::get('/edit/{id}', [PetugasDesaController::class, 'edit'])->name('edit');
                Route::put('/update/{id}', [PetugasDesaController::class, 'update'])->name('update');
            });
        });
    });

    // Monev
    Route::prefix('monev')->name('monev.')->group(function () {

        Route::prefix('laporan')->name('laporan.')->group(function () {
            // READ / VIEW
            Route::get('/', [LaporanKegiatanController::class, 'index'])->name('index');
            Route::get('/list', [LaporanKegiatanController::class, 'list'])->name('list');
            Route::get('/show/{id}', [LaporanKegiatanController::class, 'show'])->name('show');
            Route::get('/show-request', [LaporanKegiatanController::class, 'showRequest'])->name('show-request');

            // CREATE
            Route::get('/create', [LaporanKegiatanController::class, 'create'])->name('create');
            Route::post('/store', [LaporanKegiatanController::class, 'store'])->name('store');

            // EDIT / UPDATE
            Route::get('/edit', [LaporanKegiatanController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [LaporanKegiatanController::class, 'update'])->name('update');

            // DATA FETCHING (AJAX/API)
            Route::get('/get-data/{id}', [LaporanKegiatanController::class, 'getData'])->name('get-data');
            Route::get('/get-kegiatan-data', [LaporanKegiatanController::class, 'getKegiatanData'])->name('get-kegiatan-data');
        });

        Route::prefix('review')->name('review.')->group(function () {
            Route::get('/', [LaporanReviewController::class, 'index'])->name('index');
            Route::get('/list', [LaporanReviewController::class, 'list'])->name('list');
            Route::get('/review', [LaporanReviewController::class, 'review'])->name('review');
            Route::post('/submit', [LaporanReviewController::class, 'submit'])->name('submit');
        });
    });

    // Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::prefix('riwayat')->name('riwayat.')->group(function () {
            Route::get('/', [RiwayatLaporanController::class, 'index'])->name('index');
            Route::get('/list', [RiwayatLaporanController::class, 'list'])->name('list');
            Route::get('/detail', [RiwayatLaporanController::class, 'detail'])->name('detail');
            Route::get('/get-data/{id}', [RiwayatLaporanController::class, 'getData'])->name('get-data');
        });

        Route::prefix('scoring')->name('scoring.')->group(function () {
            Route::prefix('desa')->name('desa.')->group(function () {
                Route::get('/', [ScoringDesaController::class, 'index'])->name('index');
                Route::get('/list', [ScoringDesaController::class, 'list'])->name('list');
            });

            Route::prefix('kecamatan')->name('kecamatan.')->group(function () {
                Route::get('/', [ScoringKecamatanController::class, 'index'])->name('index');
                Route::get('/list', [ScoringKecamatanController::class, 'list'])->name('list');
            });
        });
    });

    // System
    Route::prefix('system')->name('system.')->group(function () {

        Route::prefix('log-activity')->name('log-activity.')->group(function () {
            Route::get('/', [LogActivityController::class, 'index'])->name('index');
            Route::get('/list', [LogActivityController::class, 'list'])->name('list');
            Route::delete('/clear', [LogActivityController::class, 'clear'])->name('clear');
        });
    });

    // RBAC
    Route::prefix('rbac')->name('rbac.')->group(function () {

        Route::prefix('role')->name('role.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/list', [RoleController::class, 'list'])->name('list');
            Route::post('/store', [RoleController::class, 'store'])->name('store');
            Route::get('/show/{id}', [RoleController::class, 'show'])->name('show');
            Route::put('/update/{id}', [RoleController::class, 'update'])->name('update');
            Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('edit');
            Route::get('/list-role-permission/{id}', [RoleController::class, 'listRolePermission'])->name('list-role-permission');
            Route::post('/store-role-permission/{id}', [RoleController::class, 'storeRolePermission'])->name('store-role-permission');
        });

        Route::prefix('permission')->name('permission.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/list', [PermissionController::class, 'list'])->name('list');
            Route::post('/sync', [PermissionSyncController::class, 'sync'])->name('sync');
            Route::post('/store', [PermissionController::class, 'store'])->name('store');
            Route::get('/show/{id}', [PermissionController::class, 'show'])->name('show');
            Route::put('/update/{id}', [PermissionController::class, 'update'])->name('update');
        });

        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/list', [UserController::class, 'list'])->name('list');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::get('/show/{id}', [UserController::class, 'show'])->name('show');
            Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
            Route::post('/update-status', [UserController::class, 'updateStatus'])->name('update-status');
            Route::post('/update-status-multiple', [UserController::class, 'updateStatusMultiple'])->name('update-status-multiple');
            Route::get('/list-user-role/{id}', [UserController::class, 'listUserRole'])->name('list-user-role');
            Route::post('/store-user-role/{id}', [UserController::class, 'storeUserRole'])->name('store-user-role');
        });
    });
});
