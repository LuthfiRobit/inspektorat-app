<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JawabanPertanyaan extends Model
{
    use HasFactory;

    protected $table = 'jawaban_pertanyaan';
    protected $primaryKey = 'id_jawaban';

    protected $fillable = [
        'laporan_id',
        'pertanyaan_id',
        'jawaban_text',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_jawaban' => 'integer',
        'laporan_id' => 'integer',
        'pertanyaan_id' => 'integer',
        'jawaban_text' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKegiatan::class, 'laporan_id', 'id_laporan');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanKegiatan::class, 'pertanyaan_kegiatan_id', 'id_pertanyaan');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenPersyaratan::class, 'jawaban_id', 'id_jawaban');
    }

    // Scopes
    public function scopeByLaporan($query, $laporanId)
    {
        return $query->where('laporan_id', $laporanId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper methods
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function hasDokumen(): bool
    {
        return $this->dokumen()->exists();
    }
}
