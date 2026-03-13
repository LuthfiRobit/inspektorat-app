<?php

namespace App\Services;

use App\Models\LaporanKegiatan;
use App\Models\JawabanPertanyaan;
use App\Models\DokumenPersyaratan;
use App\Models\Kegiatan;
use App\Models\PertanyaanKegiatan;
use App\Models\Desa;
use App\Models\HistoryLaporan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LaporanKegiatanService
{
    protected $fileUploadService;
    protected $logActivityService;
    protected $keterlambatanService;
    protected $scoringDesaService;

    public function __construct(FileUploadService $fileUploadService, LogActivityService $logActivityService, KeterlambatanService $keterlambatanService, ScoringDesaService $scoringDesaService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->logActivityService = $logActivityService;
        $this->keterlambatanService = $keterlambatanService; // Tambahkan ini
        $this->scoringDesaService = $scoringDesaService; // Tambahkan ini
    }

    /**
     * Get kegiatan data with questions and requirements
     */
    /**
     * Get kegiatan data with questions and requirements
     */
    public function getKegiatanData($desaId, $kegiatanId, $bulan = null, $tahun = null)
    {
        $desa = Desa::getRelationship($desaId);
        $kegiatan = Kegiatan::getRelationship($kegiatanId);

        if (!$kegiatan) {
            return null;
        }

        // Tentukan tahun & bulan context
        // Jika tidak dikirim, default ke master kegiatan (logic lama)
        // Tapi untuk recurring, kita butuh spesifik
        $tahun = $tahun ?? $kegiatan['tahun'];
        $bulan = $bulan ?? $kegiatan['bulan'];

        // Map Nama Bulan untuk Context
        $bulanNama = match ((int) $bulan) {
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            default => $kegiatan['nama_bulan'] // Fallback
        };

        $pertanyaan = PertanyaanKegiatan::with([
            'persyaratan' => function ($query) {
                $query->where('status', 'active')->orderBy('urutan');
            }
        ])
            ->where('kegiatan_id', $kegiatanId)
            ->where('status', 'active')
            ->orderBy('urutan')
            ->get();

        return [
            'desa' => $desa,
            // Perkaya data kegiatan dengan context
            'kegiatan' => array_merge($kegiatan, [
                'bulan_context' => $bulan, // Integer
                'tahun_context' => $tahun, // Integer
            ]),
            'context' => [
                'bulan' => $bulan,
                'bulan_nama' => $bulanNama,
                'tahun' => $tahun,
            ],
            'pertanyaan' => $pertanyaan
        ];
    }

    /**
     * Store new laporan kegiatan (Refactored for V3 Logic)
     */
    public function storeLaporan($validatedData, $jawabanData, $files = [])
    {
        return DB::transaction(function () use ($validatedData, $jawabanData, $files) {
            // Create laporan kegiatan
            $laporan = new LaporanKegiatan();
            $laporan->fill($validatedData);

            // Calculate target date using Centralized Model Logic
            $kegiatan = Kegiatan::find($validatedData['kegiatan_id']);
            if ($kegiatan) {
                // Pass $laporan object to provide context (tahun & bulan)
                $laporan->tanggal_target = LaporanKegiatan::calculateTanggalTarget($kegiatan, $laporan);
            }

            if ($validatedData['status'] == 'submitted') {
                $laporan->tanggal_submit = now();
            }

            $laporan->save();

            // Save jawaban and process files
            $this->saveJawabanDanDokumen($laporan->id_laporan, $jawabanData, $files, $validatedData['status']);

            // ===== INTEGRASI BARU: Hitung keterlambatan dan scoring =====
            if ($validatedData['status'] == 'submitted') {
                // Hitung keterlambatan
                $this->keterlambatanService->updateKeterlambatan($laporan);

                // Hitung scoring
                $this->scoringDesaService->hitungScoring($laporan);

                // Update peringkat untuk periode ini
                $this->scoringDesaService->updatePeringkat(
                    $laporan->kegiatan_id,
                    $laporan->tahun,
                    $laporan->bulan
                );
            }
            // ===== END INTEGRASI BARU =====

            return $laporan;
        });
    }

    /**
     * Update existing laporan kegiatan
     */
    public function updateLaporan($laporanId, $validatedData, $jawabanData, $files = [])
    {
        return DB::transaction(function () use ($laporanId, $validatedData, $jawabanData, $files) {
            $laporan = LaporanKegiatan::findOrFail($laporanId);
            $oldStatus = $laporan->status;

            // Update laporan
            $laporan->fill($validatedData);

            // Re-calculate target date if relevant data changed (safeguard)
            $kegiatan = $laporan->kegiatan; // Use relation loaded or find
            if (!$kegiatan) {
                // If not loaded, try to load
                $kegiatan = Kegiatan::find($laporan->kegiatan_id);
            }

            if ($kegiatan) {
                $laporan->tanggal_target = LaporanKegiatan::calculateTanggalTarget($kegiatan, $laporan);
            }

            if ($validatedData['status'] == 'submitted' && $oldStatus != 'submitted') {
                $laporan->tanggal_submit = now();
            }

            $laporan->save();

            // Record history laporan JIKA status berubah
            if ($oldStatus != $validatedData['status']) {
                $this->createLaporanHistory($laporan->id_laporan, $oldStatus, $validatedData['status'], $laporan->catatan_approval);
            }

            // Update atau buat jawaban
            $this->updateJawabanDanDokumen($laporan->id_laporan, $jawabanData, $files, $validatedData['status']);

            // Update status semua dokumen current jika status berubah
            if ($oldStatus != $validatedData['status']) {
                $this->updateAllCurrentDokumenStatus($laporan->id_laporan, $validatedData['status']);
            }

            // ===== INTEGRASI BARU: Update keterlambatan dan scoring =====
            if ($validatedData['status'] == 'submitted') {
                // Update keterlambatan
                $this->keterlambatanService->updateKeterlambatan($laporan);

                // Update scoring
                $this->scoringDesaService->hitungScoring($laporan);

                // Update peringkat
                $this->scoringDesaService->updatePeringkat(
                    $laporan->kegiatan_id,
                    $laporan->tahun,
                    $laporan->bulan
                );
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

            Log::info('Laporan history created:', [
                'laporan_id' => $laporanId,
                'from' => $statusSebelum,
                'to' => $statusSesudah,
                'catatan' => $catatanApproval
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating laporan history: ' . $e->getMessage());
        }
    }

    /**
     * Save jawaban and process files
     */
    private function saveJawabanDanDokumen($laporanId, $jawabanData, $files, $status)
    {
        foreach ($jawabanData as $pertanyaanId => $jawabanValue) {
            $jawaban = new JawabanPertanyaan();
            $jawaban->laporan_id = $laporanId;
            $jawaban->pertanyaan_id = $pertanyaanId;
            $jawaban->jawaban_text = $jawabanValue;
            $jawaban->status = $status;
            $jawaban->save();

            // Process files untuk pertanyaan ini
            if (isset($files[$pertanyaanId])) {
                $this->processFiles($files[$pertanyaanId], $laporanId, $jawaban->id_jawaban, $pertanyaanId, $status);
            }
        }
    }

    /**
     * Update jawaban and process files
     */
    private function updateJawabanDanDokumen($laporanId, $jawabanData, $files, $status)
    {
        foreach ($jawabanData as $pertanyaanId => $jawabanValue) {
            $jawaban = JawabanPertanyaan::where('laporan_id', $laporanId)
                ->where('pertanyaan_id', $pertanyaanId)
                ->firstOrNew([]);

            $jawaban->laporan_id = $laporanId;
            $jawaban->pertanyaan_id = $pertanyaanId;
            $jawaban->jawaban_text = $jawabanValue;
            $jawaban->status = $status;
            $jawaban->save();

            // Process files untuk pertanyaan ini
            if (isset($files[$pertanyaanId])) {
                $this->processFilesForUpdate($files[$pertanyaanId], $laporanId, $jawaban->id_jawaban, $pertanyaanId, $status);
            }
        }
    }

    /**
     * Process files for new laporan
     */
    private function processFiles($questionFiles, $laporanId, $jawabanId, $pertanyaanId, $status)
    {
        foreach ($questionFiles as $persyaratanId => $file) {
            if ($file && $file->isValid()) {
                // Upload pertama kali -> status selalu mengikuti laporan
                $this->saveSingleFile($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $status);
            }
        }
    }

    /**
     * Process files for laporan update
     */
    private function processFilesForUpdate($questionFiles, $laporanId, $jawabanId, $pertanyaanId, $status)
    {
        foreach ($questionFiles as $persyaratanId => $file) {
            if ($file && $file->isValid() && $file->getSize() > 0) {
                $this->handleFileRevision($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $status);
            }
        }
    }

    /**
     * Save single file (first upload)
     */
    private function saveSingleFile($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $status)
    {
        $customFileName = 'dokumen_' . $laporanId . '_' . $pertanyaanId . '_' . $persyaratanId . '_' . time() . '.' . $file->getClientOriginalExtension();

        $filePath = $this->fileUploadService->uploadSingleFile(
            $file,
            'dokumen/laporan',
            ['mimes:pdf,doc,docx', 'max:2048'],
            $customFileName
        );

        DokumenPersyaratan::create([
            'jawaban_id' => $jawabanId,
            'persyaratan_id' => $persyaratanId,
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $filePath,
            'status' => $status,
            'version' => 1,
            'is_current' => true,
            'created_by' => Auth::id()
        ]);
    }


    /**
     * Handle file revision (main logic)
     */
    private function handleFileRevision($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $laporanStatus)
    {
        // Ambil dokumen lama (current)
        $existingDokumen = DokumenPersyaratan::where('jawaban_id', $jawabanId)
            ->where('persyaratan_id', $persyaratanId)
            ->where('is_current', true)
            ->first();

        // Jika tidak ada dokumen lama, buat baru
        if (!$existingDokumen) {
            return $this->createNewDokumen($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $laporanStatus, null);
        }

        // Jika file tidak berubah
        if (!$this->isFileTrulyChanged($existingDokumen, $file, $file->getClientOriginalName())) {
            // Jika sudah approved, pertahankan status
            if ($existingDokumen->status === 'approved') {
                return;
            }

            // Jika belum approved, sesuaikan dengan status laporan
            $existingDokumen->update(['status' => $laporanStatus]);
            return;
        }

        // File berubah -> nonaktifkan versi lama
        $existingDokumen->update(['is_current' => false]);

        // Jika file baru diupload walau sebelumnya approved,
        // maka status dokumen baru mengikuti status laporan
        $newStatus = $laporanStatus;

        return $this->createNewDokumen($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $newStatus, $existingDokumen);
    }

    /**
     * Create new dokumen version
     */
    private function createNewDokumen($file, $laporanId, $jawabanId, $pertanyaanId, $persyaratanId, $status, $existingDokumen = null)
    {
        $customFileName = 'dokumen_' . $laporanId . '_' . $pertanyaanId . '_' . $persyaratanId . '_' . time() . '.' . $file->getClientOriginalExtension();

        $filePath = $this->fileUploadService->uploadSingleFile(
            $file,
            'dokumen/laporan',
            ['mimes:pdf,doc,docx', 'max:2048'],
            $customFileName
        );

        $previousVersionsCount = DokumenPersyaratan::where('jawaban_id', $jawabanId)
            ->where('persyaratan_id', $persyaratanId)
            ->count();

        return DokumenPersyaratan::create([
            'jawaban_id' => $jawabanId,
            'persyaratan_id' => $persyaratanId,
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $filePath,
            'status' => $status,
            'version' => $previousVersionsCount + 1,
            'catatan_revisi' => $existingDokumen?->catatan_revisi ?? null,
            'is_current' => true,
            'created_by' => Auth::id()
        ]);
    }

    /**
     * Check if file is truly changed
     */
    private function isFileTrulyChanged($existingDokumen, $newFile, $newFileName)
    {
        if ($existingDokumen->nama_file !== $newFileName) {
            return true;
        }

        try {
            $existingFileSize = Storage::exists($existingDokumen->path_file) ? Storage::size($existingDokumen->path_file) : 0;
            if ($existingFileSize !== $newFile->getSize()) {
                return true;
            }

            $existingFileHash = null;
            if (Storage::exists($existingDokumen->path_file)) {
                $existingFileHash = md5(Storage::get($existingDokumen->path_file));
            }

            $newFileHash = md5_file($newFile->getRealPath());

            return $existingFileHash !== $newFileHash;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Update all current dokumen status
     * (approved documents will be skipped)
     */
    private function updateAllCurrentDokumenStatus($laporanId, $newStatus)
    {
        $jawabanIds = JawabanPertanyaan::where('laporan_id', $laporanId)->pluck('id_jawaban');

        if ($jawabanIds->isNotEmpty()) {
            DokumenPersyaratan::whereIn('jawaban_id', $jawabanIds)
                ->where('is_current', true)
                ->where('status', '!=', 'approved') // ⬅️ Jangan ubah dokumen yang sudah disetujui
                ->update(['status' => $newStatus]);
        }
    }

    /**
     * @deprecated Use LaporanKegiatan::calculateTanggalTarget instead
     */
    private function calculateTargetDate($kegiatan)
    {
        // Method ini sudah digantikan oleh LaporanKegiatan::calculateTanggalTarget
        // dibiarkan kosong atau throw exception jika dipanggil
        return null;
    }
}
