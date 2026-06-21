<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\LaporanKegiatan;
use App\Models\JawabanPertanyaan;
use App\Models\DokumenPersyaratan;
use App\Models\HistoryDokumen;
use App\Models\HistoryLaporan;
use App\Models\Keterlambatan;
use App\Models\ScoringDesa;

class DevOpsController extends Controller
{
    /**
     * Purge specific laporan along with all related physical files and relational data.
     * Accessible only by Developer with specific token.
     */
    public function purgeLaporan(Request $request)
    {
        $laporanIds = $request->input('laporan_ids');

        if (empty($laporanIds) || !is_array($laporanIds)) {
            return response()->json(['error' => 'Parameter laporan_ids harus berupa array ID laporan.'], 400);
        }

        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($laporanIds as $id) {
            try {
                DB::beginTransaction();

                $laporan = LaporanKegiatan::find($id);
                if (!$laporan) {
                    $results['failed'][] = ['id' => $id, 'reason' => 'Data tidak ditemukan'];
                    DB::rollBack();
                    continue;
                }

                // Gather related keys
                $jawabanIds = JawabanPertanyaan::where('laporan_id', $id)->pluck('id_jawaban')->toArray();
                $dokumenIds = [];
                
                if (!empty($jawabanIds)) {
                    $dokumenIds = DokumenPersyaratan::whereIn('jawaban_id', $jawabanIds)->pluck('id_dokumen')->toArray();
                }

                // 1. DELETE PHYSICAL FILES
                if (!empty($dokumenIds)) {
                    // Current documents
                    $dokumens = DokumenPersyaratan::whereIn('id_dokumen', $dokumenIds)->get();
                    foreach ($dokumens as $dokumen) {
                        if ($dokumen->path_file) {
                            $filePath = public_path('uploads/' . $dokumen->path_file);
                            if (File::exists($filePath)) {
                                File::delete($filePath);
                            }
                        }
                    }

                    // History documents
                    $historyDokumens = HistoryDokumen::whereIn('dokumen_id', $dokumenIds)->get();
                    foreach ($historyDokumens as $hd) {
                        if ($hd->path_file_sebelum) {
                            $filePath = public_path('uploads/' . $hd->path_file_sebelum);
                            if (File::exists($filePath)) {
                                File::delete($filePath);
                            }
                        }
                    }
                }

                // 2. DELETE DB RECORDS (Bottom-Up Cascade)
                if (!empty($dokumenIds)) {
                    HistoryDokumen::whereIn('dokumen_id', $dokumenIds)->delete();
                    DokumenPersyaratan::whereIn('id_dokumen', $dokumenIds)->delete();
                }

                if (!empty($jawabanIds)) {
                    JawabanPertanyaan::whereIn('id_jawaban', $jawabanIds)->delete();
                }

                HistoryLaporan::where('laporan_id', $id)->delete();
                Keterlambatan::where('laporan_id', $id)->delete();
                ScoringDesa::where('laporan_id', $id)->delete();
                
                // Finally delete parent
                $laporan->delete();

                DB::commit();
                $results['success'][] = $id;
                
                Log::warning('DEVELOPER PURGE: Laporan ID ' . $id . ' and all related data/files have been permanently deleted via Hidden API.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('DEVELOPER PURGE ERROR for Laporan ID ' . $id . ': ' . $e->getMessage());
                $results['failed'][] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        return response()->json([
            'message' => 'Proses purge laporan selesai.',
            'data' => $results
        ]);
    }
}
