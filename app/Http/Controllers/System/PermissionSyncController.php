<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PermissionSyncController extends Controller
{

    protected $responseService;
    protected $transactionService;

    /**
     * PermissionSyncController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
    }

    /**
     * Execute permission synchronization using Artisan command.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request)
    {
        try {
            // Run the custom Artisan command to sync permissions
            Artisan::call('permission:sync');
            $output = Artisan::output();

            // Log user activity
            LogActivityService::log('Synced permissions from routes using PermissionSyncController');

            // Return success response
            return $this->responseService->success([
                'message' => 'Permissions synced successfully.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            // Log error details
            Log::error('Permission sync failed.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Log::error('Failed to sync permissions: ' . $e->getMessage());
            return $this->responseService->error($e->getMessage());
        }
    }

    // public function sync(Request $request)
    // {
    //     Artisan::call('permission:sync');

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Permission synced successfully!',
    //         'output' => Artisan::output()
    //     ]);
    // }
}
