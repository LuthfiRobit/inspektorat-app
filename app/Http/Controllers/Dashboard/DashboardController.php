<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;

    /**
     * DesaController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService,  LogActivityService $logActivityService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
    }

    /**
     * Display the index view for the dashboard.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $petugas = $user->petugas;

        // Jika user memiliki relasi petugas
        if ($petugas) {
            if (!empty($petugas->desa_id)) {
                // Dashboard untuk petugas desa
                $this->logActivityService->log('Accessed the Desa Dashboard');
                return view('administration.dashboard.desa');
            }

            if (!empty($petugas->kecamatan_id)) {
                // Dashboard untuk petugas kecamatan
                $this->logActivityService->log('Accessed the Kecamatan Dashboard');
                return view('administration.dashboard.kecamatan');
            }
        }

        // Dashboard default (Inspektorat / Developer / Admin)
        $this->logActivityService->log('Accessed the Main Dashboard');
        return view('administration.dashboard.index');
    }
}
