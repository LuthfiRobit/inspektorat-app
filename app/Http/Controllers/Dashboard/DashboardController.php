<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
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
    protected $dashboardService;

    /**
     * DesaController constructor.
     *
     * @param ResponseService $responseService
     * @param TransactionService $transactionService
     * @param LogActivityService $logActivityService
     */
    public function __construct(ResponseService $responseService, TransactionService $transactionService,  LogActivityService $logActivityService, DashboardService $dashboardService)
    {
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->logActivityService = $logActivityService;
        $this->dashboardService = $dashboardService;
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

    /**
     * Get dashboard summary data (single endpoint for all roles)
     */
    public function getSummary(Request $request)
    {
        try {
            $filters = $request->only([
                'tahun', 
                'bulan', 
                'jenis_kegiatan_id',
                'kecamatan_id', 
                'desa_id'
            ]);
            
            $data = $this->dashboardService->getSummary($filters);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data dashboard berhasil diambil'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

     /**
     * Get recent laporan for tables
     */
    public function getRecentLaporan(Request $request, $type = 'pending')
    {
        try {
            $filters = $request->only(['tahun', 'bulan', 'jenis_kegiatan_id']);
            
            $data = $this->dashboardService->getRecentLaporan($filters, $type, 5);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data laporan berhasil diambil'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUpcommingKegiatan(Request $request)
{
    try {
        $filters = $request->only(['tahun', 'bulan', 'jenis_kegiatan_id', 'desa_id']);
        $data = $this->dashboardService->getUpcommingKegiatan($filters);
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data kegiatan berhasil diambil'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
}
