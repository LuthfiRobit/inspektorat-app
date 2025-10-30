<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LogActivityController extends Controller
{
    protected $responseService;
    protected $transactionService;

    /**
     * LogActivityController constructor.
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
     * Display the index view for LogActivity.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('administration.system.logActivity.index');
    }

    /**
     * Retrieve and return the list of LogActivity for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $query = LogActivity::select(
            'action',
            'description',
            'ip_address',
            'user_agent',
            'log_activity.created_at',
            'users.name'
        )
            ->leftJoin('users', 'users.id_user', '=', 'log_activity.user_id')
            ->orderBy('log_activity.created_at', 'DESC')->get();

        LogActivityService::log('Fetched Log Acitvity list');

        return DataTables::of($query)
            ->editColumn('name', function ($item) {
                return $item->name != null ? strtoupper($item->name) : 'SISTEM';
            })
            ->rawColumns(['name'])
            ->make(true);
    }

    /**
     * Clear all log activities.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        try {
            // Directly truncate without transaction
            DB::table('log_activity')->truncate();

            return $this->responseService->success(null, 'Logs cleared successfully');
        } catch (Exception $e) {
            Log::error('Transaction failed: ' . $e->getMessage());
            return $this->responseService->error($e->getMessage());
        }
    }
}
