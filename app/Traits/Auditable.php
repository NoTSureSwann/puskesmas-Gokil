<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * @method static void created(\Closure|string|array $callback)
 * @method static void updated(\Closure|string|array $callback)
 * @method static void deleted(\Closure|string|array $callback)
 */
trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    protected function logAudit(string $event)
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getChanges();
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        DB::table('audit_logs')->insert([
            'user_id' => Auth::check() ? Auth::id() : null,
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'url' => \Illuminate\Support\Facades\Request::fullUrl(),
            'ip_address' => \Illuminate\Support\Facades\Request::ip(),
            'user_agent' => \Illuminate\Support\Facades\Request::userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
