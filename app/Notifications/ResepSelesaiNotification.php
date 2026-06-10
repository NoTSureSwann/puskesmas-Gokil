<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\ResepSiapMail;
use App\Models\Resep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ResepSelesaiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Resep $resep
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): ResepSiapMail
    {
        return (new ResepSiapMail($this->resep))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'resep_id' => $this->resep->id,
            'no_resep' => $this->resep->no_resep,
            'dokter_name' => $this->resep->dokter->user->name,
            'message' => 'Resep obat Anda dengan nomor ' . $this->resep->no_resep . ' telah selesai diproses.',
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'resep_id' => $this->resep->id,
            'no_resep' => $this->resep->no_resep,
            'message' => 'Resep obat Anda dengan nomor ' . $this->resep->no_resep . ' telah selesai diproses.',
        ]);
    }
}
