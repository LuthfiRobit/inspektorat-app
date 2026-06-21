<?php

namespace App\Services;

use App\Models\LaporanKegiatan;
use App\Models\HistoryLaporan;
use App\Models\DokumenPersyaratan;
use App\Models\JawabanPertanyaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaporanReviewService
{
    protected $logActivityService;
    protected $keterlambatanService;
    protected $scoringDesaService;

    public function __construct(LogActivityService $logActivityService, KeterlambatanService $keterlambatanService, ScoringDesaService $scoringDesaService)
    {
        $this->logActivityService = $logActivityService;
        $this->keterlambatanService = $keterlambatanService; // Tambahkan ini
        $this->scoringDesaService = $scoringDesaService; // Tambahkan ini
    }

    /**
     * Submit review for laporan kegiatan
     */
    public function submitReview($laporanId, $validatedData, $dokumenStatus, $catatanRevisi)
    {
        return DB::transaction(function () use ($laporanId, $validatedData, $dokumenStatus, $catatanRevisi) {
            $laporan = LaporanKegiatan::findOrFail($laporanId);
            $oldStatus = $laporan->status;

            // Update laporan status and approval info
            $laporan->fill([
                'status' => $validatedData['status'],
                'catatan_approval' => $validatedData['catatan_approval'] ?? null,
                'approved_by' => Auth::id(),
                'tanggal_approve' => now()
            ]);
            $laporan->save();

            // Record history laporan JIKA status berubah
            if ($oldStatus != $validatedData['status']) {
                $this->createLaporanHistory($laporan->id_laporan, $oldStatus, $validatedData['status'], $validatedData['catatan_approval'] ?? null);
            }

            // Process dokumen review (status and revision notes) - TANPA BUAT VERSI BARU
            $this->processDokumenReview($laporan->id_laporan, $dokumenStatus, $catatanRevisi);

            // ===== INTEGRASI BARU: Update scoring saat approve =====
            if ($validatedData['status'] == 'approved') {
                // Update scoring (karena status dokumen mungkin berubah)
                $this->scoringDesaService->hitungScoring($laporan);

                // Update peringkat
                $this->scoringDesaService->updatePeringkat(
                    $laporan->kegiatan_id,
                    $laporan->tahun,
                    $laporan->bulan
                );
            }

            // Send Notification to Kecamatan
            if (in_array($validatedData['status'], ['approved', 'revision'])) {
                $this->sendNotificationToKecamatan($laporan);
            }

            // ===== END INTEGRASI BARU =====
            return $laporan;
        });
    }

    /**
     * Create history record for laporan status change
     */
    public function createLaporanHistory($laporanId, $statusSebelum, $statusSesudah, $catatanApproval = null)
    {
        try {
            $historyLaporan = new HistoryLaporan();
            $historyLaporan->laporan_id = $laporanId;
            $historyLaporan->status_sebelum = $statusSebelum;
            $historyLaporan->status_sesudah = $statusSesudah;
            $historyLaporan->catatan_perubahan = $catatanApproval;
            $historyLaporan->changed_by = Auth::id();
            $historyLaporan->save();

            Log::info('Laporan review history created:', [
                'laporan_id' => $laporanId,
                'from' => $statusSebelum,
                'to' => $statusSesudah,
                'reviewer_id' => Auth::id()
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating laporan review history: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process dokumen review - update status and revision notes (TANPA BUAT VERSI BARU)
     */
    private function processDokumenReview($laporanId, $dokumenStatus, $catatanRevisi)
    {
        foreach ($dokumenStatus as $questionId => $requirements) {
            foreach ($requirements as $requirementId => $status) {
                $this->updateDokumenStatus(
                    $laporanId,
                    $questionId,
                    $requirementId,
                    $status,
                    $catatanRevisi[$questionId][$requirementId] ?? null
                );
            }
        }
    }

    /**
     * Update dokumen status and revision notes (TANPA BUAT VERSI BARU)
     */
    private function updateDokumenStatus($laporanId, $questionId, $requirementId, $status, $catatanRevisi)
    {
        // Get the jawaban for this question and laporan
        $jawaban = JawabanPertanyaan::where('laporan_id', $laporanId)
            ->where('pertanyaan_id', $questionId)
            ->first();

        if (!$jawaban) {
            Log::warning('Jawaban not found for dokumen review', [
                'laporan_id' => $laporanId,
                'pertanyaan_id' => $questionId,
                'persyaratan_id' => $requirementId
            ]);
            return;
        }

        // Get current dokumen
        $currentDokumen = DokumenPersyaratan::where('jawaban_id', $jawaban->id_jawaban)
            ->where('persyaratan_id', $requirementId)
            ->where('is_current', true)
            ->first();

        if (!$currentDokumen) {
            Log::warning('Current dokumen not found for review', [
                'jawaban_id' => $jawaban->id_jawaban,
                'persyaratan_id' => $requirementId
            ]);
            return;
        }

        // Validasi: Jika status revision, catatan revisi wajib
        if ($status === 'revision' && empty($catatanRevisi)) {
            Log::warning('Catatan revisi wajib untuk status revision', [
                'dokumen_id' => $currentDokumen->id_dokumen,
                'persyaratan_id' => $requirementId
            ]);
            // Tetap lanjutkan, karena validasi sudah dilakukan di controller
        }

        // UPDATE METADATA SAJA - TANPA BUAT VERSI BARU
        $updateData = [
            'status' => $status,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now()
        ];

        // Untuk status revision, simpan catatan revisi
        if ($status === 'revision') {
            $updateData['catatan_revisi'] = $catatanRevisi;
        } else {
            // Untuk status approved, hapus catatan revisi (jika ada dari sebelumnya)
            $updateData['catatan_revisi'] = null;
        }

        $currentDokumen->update($updateData);

        Log::info('Dokumen review updated (metadata only)', [
            'dokumen_id' => $currentDokumen->id_dokumen,
            'status_persetujuan' => $status,
            'has_catatan' => !empty($catatanRevisi),
            'reviewer_id' => Auth::id()
        ]);
    }

    /**
     * Update all current dokumen status for a laporan
     */
    public function updateAllCurrentDokumenStatus($laporanId, $newStatus)
    {
        $jawabanIds = JawabanPertanyaan::where('laporan_id', $laporanId)->pluck('id_jawaban');

        if ($jawabanIds->isNotEmpty()) {
            $updated = DokumenPersyaratan::whereIn('jawaban_id', $jawabanIds)
                ->where('is_current', true)
                ->update(['status' => $newStatus]);

            Log::info('Updated all current dokumen status', [
                'laporan_id' => $laporanId,
                'new_status' => $newStatus,
                'affected_dokumen' => $updated
            ]);
        }
    }

    /**
     * Send notification to Kecamatan when a report is reviewed
     */
    protected function sendNotificationToKecamatan($laporan)
    {
        try {
            // Find Petugas Kecamatan assigned to this desa
            $petugasIds = \App\Models\PetugasWilayahBinaan::where('desa_id', $laporan->desa_id)
                ->pluck('petugas_id');

            // Get Users associated with these petugas
            $users = \App\Models\User::whereIn('id_user', function ($query) use ($petugasIds) {
                $query->select('user_id')
                    ->from('petugas')
                    ->whereIn('id_petugas', $petugasIds)
                    ->whereNotNull('kecamatan_id') // Ensure it is kecamatan level
                    ->whereNotNull('user_id');
            })->get();

            // Send notification
            \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\LaporanReviewedNotification($laporan));
            
            Log::info('Sent LaporanReviewedNotification to Kecamatan users.', ['laporan_id' => $laporan->id_laporan, 'user_count' => $users->count()]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification to Kecamatan: ' . $e->getMessage());
        }
    }
}
