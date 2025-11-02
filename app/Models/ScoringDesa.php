<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringDesa extends Model
{
    use HasFactory;

    protected $table = 'scoring_desa';
    protected $primaryKey = 'id_scoring';

    protected $fillable = [
        'desa_id',
        'laporan_id',
        'kegiatan_id',
        'tahun',
        'bulan',
        'total_persyaratan_wajib',
        'persyaratan_terpenuhi',
        'persentase_kelengkapan',
        'skor_ketepatan_waktu',
        'total_skor',
        'peringkat'
    ];

    protected $casts = [
        'persentase_kelengkapan' => 'decimal:2',
        'tahun' => 'integer',
        'bulan' => 'integer',
    ];

    // Relationships
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id', 'id_desa');
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKegiatan::class, 'laporan_id', 'id_laporan');
    }

    // Scopes
    public function scopeByDesa($query, $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    public function scopeByPeriode($query, $tahun, $bulan = null)
    {
        $query->where('tahun', $tahun);
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        return $query;
    }

    public function scopeRanked($query)
    {
        return $query->whereNotNull('peringkat')->orderBy('peringkat', 'ASC');
    }

    public function scopeTopRank($query, $limit = 10)
    {
        return $query->ranked()->limit($limit);
    }

    // Helper methods
    public function getPersentaseKelengkapanFormatted(): string
    {
        return number_format($this->persentase_kelengkapan, 2) . '%';
    }

    public function getGrade(): string
    {
        if ($this->total_skor >= 90) return 'A';
        if ($this->total_skor >= 80) return 'B';
        if ($this->total_skor >= 70) return 'C';
        if ($this->total_skor >= 60) return 'D';
        return 'E';
    }

    public function getGradeColor(): string
    {
        return [
            'A' => 'success',
            'B' => 'primary',
            'C' => 'warning',
            'D' => 'info',
            'E' => 'danger'
        ][$this->getGrade()] ?? 'secondary';
    }

    public function getNamaBulanAttribute(): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ][$this->bulan] ?? 'Unknown';
    }

    // Tambahkan relationship
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id', 'id_kegiatan');
    }

    // Scope untuk filter per kegiatan
    public function scopeByKegiatan($query, $kegiatanId)
    {
        return $query->where('kegiatan_id', $kegiatanId);
    }

    // Scope untuk ranking per kegiatan
    public function scopeRankedByKegiatan($query, $kegiatanId, $tahun = null, $bulan = null)
    {
        $query->where('kegiatan_id', $kegiatanId);

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        return $query->whereNotNull('peringkat')->orderBy('peringkat', 'ASC');
    }
}
