<?php

namespace App\Providers;

use App\Services\FileUploadService;
use App\Services\LaporanKegiatanService;
use App\Services\LogActivityService;
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
                $app->make(LogActivityService::class)
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
