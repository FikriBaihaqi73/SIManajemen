<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyGoal extends Model
{
    use HasFactory;

    protected $fillable = ['ceo_id', 'goal', 'year'];

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }
}
