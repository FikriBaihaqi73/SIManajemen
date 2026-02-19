<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPlan extends Model
{
    use HasFactory;

    protected $fillable = ['key_result_id', 'activity', 'is_completed'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function keyResult()
    {
        return $this->belongsTo(KeyResult::class);
    }
}
