<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    use HasFactory;

    protected $fillable = ['ceo_id', 'company_goal_id', 'division', 'objective'];

    protected $appends = ['progress'];

    public function companyGoal()
    {
        return $this->belongsTo(CompanyGoal::class);
    }

    public function keyResults()
    {
        return $this->hasMany(KeyResult::class);
    }

    public function getProgressAttribute()
    {
        $krs = $this->keyResults;
        
        if ($krs->isEmpty()) {
            return 0;
        }

        $totalWeightedProgress = 0;
        $totalWeight = 0;

        foreach ($krs as $kr) {
            $progress = ($kr->target > 0) ? ($kr->actual / $kr->target) * 100 : 0;
            // Cap at 100% per KR if desired, or let it exceed
            $progress = min($progress, 100); 
            
            $totalWeightedProgress += $progress * $kr->weight;
            $totalWeight += $kr->weight;
        }

        return ($totalWeight > 0) ? round($totalWeightedProgress / $totalWeight, 1) : 0;
    }
}
