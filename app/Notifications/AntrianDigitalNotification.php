<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\NomorAntrianMail;
use App\Models\Kunjungan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AntrianDigitalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Kunjungan $kunjungan
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): NomorAntrianMail
    {
        return (new NomorAntrianMail($this->kunjungan))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kunjungan_id' => $this->kunjungan->id,
            'no_kunjungan' => $this->kunjungan->no_kunjungan,
            'no_antrian' => $this->kunjungan->no_antrian,
            'poli_name' => $this->kunjungan->poli->nama_poli,
            'tanggal_kunjungan' => \Carbon\Carbon::parse($this->kunjungan->tanggal_kunjungan)->format('Y-m-d'),
            'message' => 'Pendaftaran antrian Anda berhasil. Nomor Antrian: ' . str_pad((string)$this->kunjungan->no_antrian, 3, '0', STR_PAD_LEFT),
        ];
    }
}
