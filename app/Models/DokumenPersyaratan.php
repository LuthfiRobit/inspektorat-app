<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPersyaratan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_persyaratan';
    protected $primaryKey = 'id_dokumen';

    protected $fillable = [
        'jawaban_id',
        'persyaratan_id',
        'nama_file',
        'path_file',
        'status',
        'catatan_revisi',
        'version',
        'is_current',
        'uploaded_by'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'version' => 'integer',
    ];

    // Relationships
    public function jawaban(): BelongsTo
    {
        return $this->belongsTo(JawabanPertanyaan::class, 'jawaban_id', 'id_jawaban');
    }

    public function persyaratan(): BelongsTo
    {
        return $this->belongsTo(Persyaratan::class, 'persyaratan_id', 'id_persyaratan');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id_user');
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByJawaban($query, $jawabanId)
    {
        return $query->where('jawaban_id', $jawabanId);
    }

    // Helper methods
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    public function getFileExtension(): string
    {
        return pathinfo($this->nama_file, PATHINFO_EXTENSION);
    }

    public function getFileSizeFormatted(): string
    {
        // Implementasi untuk mendapatkan ukuran file
        if (file_exists(storage_path('app/' . $this->path_file))) {
            $size = filesize(storage_path('app/' . $this->path_file));
            return $this->formatBytes($size);
        }
        return 'Unknown';
    }

    private function formatBytes($size, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
}
