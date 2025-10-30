<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryDokumen extends Model
{
    use HasFactory;

    protected $table = 'history_dokumen';
    protected $primaryKey = 'id_history_dokumen';

    protected $fillable = [
        'dokumen_id',
        'action',
        'nama_file_sebelum',
        'path_file_sebelum',
        'catatan_perubahan',
        'changed_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(DokumenPersyaratan::class, 'dokumen_id', 'id_dokumen');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id_user');
    }

    // Scopes
    public function scopeByDokumen($query, $dokumenId)
    {
        return $query->where('dokumen_id', $dokumenId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'DESC')->limit($limit);
    }

    // Helper methods
    public function getActionText(): string
    {
        return [
            'upload' => 'Upload',
            'revision' => 'Revisi',
            'approve' => 'Disetujui',
            'reject' => 'Ditolak'
        ][$this->action] ?? $this->action;
    }

    public function getActionColor(): string
    {
        return [
            'upload' => 'primary',
            'revision' => 'warning',
            'approve' => 'success',
            'reject' => 'danger'
        ][$this->action] ?? 'secondary';
    }

    public function getActionIcon(): string
    {
        return [
            'upload' => 'fa-upload',
            'revision' => 'fa-edit',
            'approve' => 'fa-check',
            'reject' => 'fa-times'
        ][$this->action] ?? 'fa-file';
    }

    public function hasPreviousFile(): bool
    {
        return !empty($this->nama_file_sebelum) && !empty($this->path_file_sebelum);
    }
}
