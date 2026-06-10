<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Obat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StokObatRendahNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Obat $obat
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Peringatan: Stok Obat Rendah - SI Puskesmas & Klinik Sehat Sentosa')
            ->line('Kami mendeteksi stok obat berikut telah mencapai atau berada di bawah batas minimum:')
            ->line('**Nama Obat:** ' . $this->obat->nama_obat)
            ->line('**Kode Obat:** ' . $this->obat->kode_obat)
            ->line('**Sisa Stok:** ' . $this->obat->stok . ' ' . $this->obat->satuan)
            ->line('**Batas Minimum:** ' . $this->obat->stok_minimum)
            ->action('Kelola Stok Obat', url('/admin/obat'))
            ->line('Mohon segera lakukan pengadaan atau restock untuk obat tersebut.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'obat_id' => $this->obat->id,
            'kode_obat' => $this->obat->kode_obat,
            'nama_obat' => $this->obat->nama_obat,
            'stok' => $this->obat->stok,
            'stok_minimum' => $this->obat->stok_minimum,
            'message' => 'Stok obat ' . $this->obat->nama_obat . ' hampir habis (' . $this->obat->stok . ' ' . $this->obat->satuan . ').',
        ];
    }
}
