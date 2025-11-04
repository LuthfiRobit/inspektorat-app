<?php

namespace App\Providers;

use App\Repositories\KeterlambatanRepository;
use App\Repositories\ScoringDesaRepository;
use App\Services\FileUploadService;
use App\Services\KeterlambatanService;
use App\Services\LaporanKegiatanService;
use App\Services\LogActivityService;
use App\Services\ScoringDesaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LaporanKegiatanService::class, function ($app) {
            return new LaporanKegiatanService(
                $app->make(FileUploadService::class),
                $app->make(LogActivityService::class),
                $app->make(KeterlambatanService::class),
                $app->make(ScoringDesaService::class)
            );
        });

        $this->app->bind(KeterlambatanService::class, function ($app) {
            return new KeterlambatanService($app->make(KeterlambatanRepository::class));
        });

        $this->app->bind(ScoringDesaService::class, function ($app) {
            return new ScoringDesaService(
                $app->make(ScoringDesaRepository::class),
                $app->make(KeterlambatanService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
