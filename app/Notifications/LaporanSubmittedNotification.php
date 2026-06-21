<?php

namespace App\Notifications;

use App\Models\LaporanKegiatan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class LaporanSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $laporan;
    protected $desa;
    protected $kegiatan;

    /**
     * Create a new notification instance.
     */
    public function __construct(LaporanKegiatan $laporan)
    {
        $this->laporan = $laporan;
        // Load related models
        $this->desa = $laporan->desa;
        $this->kegiatan = $laporan->kegiatan;
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
        $url = route('administrator.monev.review.review', [
            'kegiatan_id' => $this->laporan->kegiatan_id,
            'id_laporan' => $this->laporan->id_laporan
        ]);

        return (new MailMessage)
                    ->subject('Laporan Baru Butuh Review: ' . ($this->kegiatan->nama_kegiatan ?? ''))
                    ->view('emails.laporan_submitted', [
                        'kegiatanName' => $this->kegiatan->nama_kegiatan ?? '-',
                        'desaName'     => $this->desa->nama_desa ?? '-',
                        'periode'      => $this->laporan->bulan . '/' . $this->laporan->tahun,
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
        $url = route('administrator.monev.review.review', [
            'kegiatan_id' => $this->laporan->kegiatan_id,
            'id_laporan' => $this->laporan->id_laporan
        ]);

        return [
            'title' => 'Laporan Baru Disubmit',
            'message' => 'Laporan ' . ($this->kegiatan->nama_kegiatan ?? '') . ' dari Desa ' . ($this->desa->nama_desa ?? '') . ' menunggu review.',
            'url' => $url,
            'type' => 'info',
            'laporan_id' => $this->laporan->id_laporan
        ];
    }
}
