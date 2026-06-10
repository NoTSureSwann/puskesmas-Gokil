<?php

namespace App\Events;

use App\Models\KeluargaPasien;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyHealthUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $keluarga;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(KeluargaPasien $keluarga, string $message)
    {
        $this->keluarga = $keluarga;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast secara privat ke channel pasien utama
        return [
            new PrivateChannel('pasien.' . $this->keluarga->pasien_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'family.health.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->keluarga->id,
            'nama_lengkap' => $this->keluarga->nama_lengkap,
            'pesan_update' => $this->message,
        ];
    }
}
