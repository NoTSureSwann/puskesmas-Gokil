<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Resep;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResepBaru implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Resep $resep
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('farmasi-dashboard'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->resep->id,
            'no_resep' => $this->resep->no_resep,
            'pasien_name' => $this->resep->kunjungan->pasien->user->name,
            'dokter_name' => $this->resep->dokter->user->name,
            'prioritas' => $this->resep->prioritas,
            'status' => $this->resep->status,
            'jam_input' => $this->resep->jam_input_resep->format('H:i'),
            'item_count' => $this->resep->detailResep()->count(),
        ];
    }
}
