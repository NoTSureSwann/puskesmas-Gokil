<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_feedbacks';

    protected $fillable = [
        'ai_dataset_id',
        'user_id',
        'reward_score',
        'corrected_poli',
        'notes',
    ];

    public function dataset()
    {
        return $this->belongsTo(AiDataset::class, 'ai_dataset_id');
    }
}
