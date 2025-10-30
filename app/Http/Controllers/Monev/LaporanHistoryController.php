<?php

namespace App\Http\Controllers\Monev;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class LaporanHistoryController extends Controller
{
    protected $responseService;
    protected $transactionService;
    protected $logActivityService;
    protected $fileUploadService;

    /**
     * LaporanHistoryController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService,  LogActivityService $logActivityService,  FileUploadService $fileUploadService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display the index view for Laporan History.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->logActivityService->log('Accessed the index view for Laporan History');
        return view('administration.monev.historyLaporan.index');
    }
}
