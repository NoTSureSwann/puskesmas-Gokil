<?php

namespace App\Observers;

use App\Models\AiDataset;

class AiDatasetObserver
{
    /**
     * Handle the AiDataset "created" event.
     */
    public function created(AiDataset $aiDataset): void
    {
        // Real-time Geospasial Map Update
        \Illuminate\Support\Facades\Artisan::call('ai:process-outbreaks');
    }

    /**
     * Handle the AiDataset "updated" event.
     */
    public function updated(AiDataset $aiDataset): void
    {
        //
    }

    /**
     * Handle the AiDataset "deleted" event.
     */
    public function deleted(AiDataset $aiDataset): void
    {
        //
    }

    /**
     * Handle the AiDataset "restored" event.
     */
    public function restored(AiDataset $aiDataset): void
    {
        //
    }

    /**
     * Handle the AiDataset "force deleted" event.
     */
    public function forceDeleted(AiDataset $aiDataset): void
    {
        //
    }
}
