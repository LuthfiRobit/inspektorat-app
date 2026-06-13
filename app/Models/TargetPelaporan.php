<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TargetPelaporan extends Model
{
    /**
     * The table associated with the model.
     * This is mapped to the MySQL VIEW.
     *
     * @var string
     */
    protected $table = 'v_target_pelaporan';

    /**
     * Primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    // We can cast attributes to specific types
    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'kegiatan_id' => 'integer',
        'desa_id' => 'integer',
        'batas_akhir_upload' => 'integer',
    ];

    /**
     * Accessor for 'tanggal_target'
     * Reuses the existing logic but only executes on the loaded subset of rows.
     */
    public function getTanggalTargetAttribute()
    {
        if ($this->db_tanggal_target) {
            return $this->db_tanggal_target;
        }

        if (!$this->batas_akhir_upload) {
            return null;
        }

        try {
            $baseDate = Carbon::create($this->tahun, $this->bulan, 1);
            $tanggalSelesai = $this->tanggal_selesai ? (int)$this->tanggal_selesai : $baseDate->daysInMonth;
            $tanggalSelesai = min($tanggalSelesai, $baseDate->daysInMonth);

            return Carbon::create($this->tahun, $this->bulan, $tanggalSelesai)
                ->addDays($this->batas_akhir_upload)
                ->startOfDay()
                ->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Accessor for 'days_until_deadline'
     */
    public function getDaysUntilDeadlineAttribute()
    {
        $targetStr = $this->tanggal_target;
        if (!$targetStr) return 0;
        
        return Carbon::parse($targetStr)->diffInDays(now(), false);
    }

    /**
     * Accessor for 'timeline_status'
     */
    public function getTimelineStatusAttribute()
    {
        if (!$this->tanggal_target) {
            return 'Menunggu';
        }

        $tanggalTarget = Carbon::parse($this->tanggal_target);
        $comparisonDate = $this->tanggal_submit ? Carbon::parse($this->tanggal_submit) : now();
        $gracePeriod = $tanggalTarget->copy()->addDays(1);

        if (in_array($this->status, ['submitted', 'approved'])) {
            return $comparisonDate->gt($gracePeriod) ? 'Terlambat' : 'Tepat Waktu';
        } else {
            if (now()->gt($gracePeriod)) return 'Terlambat';
            if (now()->gt($tanggalTarget)) return 'Tenggang';
            return 'Menunggu';
        }
    }

    /**
     * Accessor for 'status_display'
     */
    public function getStatusDisplayAttribute()
    {
        return LaporanKegiatan::getStatusDisplayForUser($this->status);
    }

    /**
     * Accessor for 'status_class'
     */
    public function getStatusClassAttribute()
    {
        return LaporanKegiatan::getStatusClass($this->status);
    }

    /**
     * Accessor for 'timeline_dates'
     */
    public function getTimelineDatesAttribute()
    {
        return $this->tanggal_target 
            ? 'Target: ' . Carbon::parse($this->tanggal_target)->format('d M Y') 
            : '';
    }
}
