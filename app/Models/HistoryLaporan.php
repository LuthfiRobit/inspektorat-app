<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryLaporan extends Model
{
    use HasFactory;

    protected $table = 'history_laporan';
    protected $primaryKey = 'id_history';

    protected $fillable = [
        'laporan_id',
        'status_sebelum',
        'status_sesudah',
        'catatan_perubahan',
        'changed_by'
    ];

    // Relationships
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKegiatan::class, 'laporan_id', 'id_laporan');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id_user');
    }

    // Scope untuk filter berdasarkan laporan
    public function scopeByLaporan($query, $laporanId)
    {
        return $query->where('laporan_id', $laporanId);
    }
}
