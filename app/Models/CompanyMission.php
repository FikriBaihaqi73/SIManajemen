<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMission extends Model
{
    protected $fillable = ['ceo_id', 'content', 'order'];

    public function user()
    {
        return $this->belongsTo(User::class, 'ceo_id');
    }
}
