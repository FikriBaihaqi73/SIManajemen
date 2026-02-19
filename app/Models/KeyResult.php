<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyResult extends Model
{
    use HasFactory;

    protected $fillable = ['objective_id', 'key_result', 'target', 'actual', 'unit', 'weight'];

    protected $appends = ['progress'];

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function actionPlans()
    {
        return $this->hasMany(ActionPlan::class);
    }

    public function getProgressAttribute()
    {
        if ($this->target == 0) return 0;
        return round(($this->actual / $this->target) * 100, 1);
    }
}
