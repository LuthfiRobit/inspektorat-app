<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DevOpsController;

// Hidden route for developers to purge laporan
// Requires VerifyDeveloperKeyMiddleware
Route::post('/v1/system/dev-ops/purge-laporan', [DevOpsController::class, 'purgeLaporan'])
    ->middleware('devops.verify');
