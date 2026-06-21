<?php

namespace App\Notifications;

use App\Models\LaporanKegiatan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class LaporanReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $laporan;
    protected $desa;
    protected $kegiatan;
    protected $statusReview;

    /**
     * Create a new notification instance.
     */
    public function __construct(LaporanKegiatan $laporan)
    {
        $this->laporan = $laporan;
        $this->desa = $laporan->desa;
        $this->kegiatan = $laporan->kegiatan;
        
        $this->statusReview = $laporan->status === 'approved' ? 'Disetujui' : 'Revisi';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->laporan->status === 'approved') {
            $url = route('administrator.monitoring.riwayat.detail', [
                'desa_id' => $this->laporan->desa_id,
                'kegiatan_id' => $this->laporan->kegiatan_id,
                'id_laporan' => $this->laporan->id_laporan
            ]);
        } else {
            $url = route('administrator.monev.laporan.edit', [
                'desa_id' => $this->laporan->desa_id,
                'kegiatan_id' => $this->laporan->kegiatan_id,
                'id_laporan' => $this->laporan->id_laporan
            ]);
        }

        $subject = 'Hasil Review Laporan: ' . ($this->kegiatan->nama_kegiatan ?? '') . ' - ' . $this->statusReview;

        return (new MailMessage)
                    ->subject($subject)
                    ->view('emails.laporan_reviewed', [
                        'kegiatanName' => $this->kegiatan->nama_kegiatan ?? '-',
                        'desaName'     => $this->desa->nama_desa ?? '-',
                        'periode'      => $this->laporan->bulan . '/' . $this->laporan->tahun,
                        'statusReview' => $this->statusReview,
                        'url'          => $url,
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->laporan->status === 'approved') {
            $url = route('administrator.monitoring.riwayat.detail', [
                'desa_id' => $this->laporan->desa_id,
                'kegiatan_id' => $this->laporan->kegiatan_id,
                'id_laporan' => $this->laporan->id_laporan
            ]);
        } else {
            $url = route('administrator.monev.laporan.edit', [
                'desa_id' => $this->laporan->desa_id,
                'kegiatan_id' => $this->laporan->kegiatan_id,
                'id_laporan' => $this->laporan->id_laporan
            ]);
        }

        return [
            'title' => 'Hasil Review Laporan: ' . $this->statusReview,
            'message' => 'Laporan ' . ($this->kegiatan->nama_kegiatan ?? '') . ' untuk Desa ' . ($this->desa->nama_desa ?? '') . ' telah direview dengan status: ' . $this->statusReview . '.',
            'url' => $url,
            'type' => $this->laporan->status === 'approved' ? 'success' : 'warning',
            'laporan_id' => $this->laporan->id_laporan
        ];
    }
}
