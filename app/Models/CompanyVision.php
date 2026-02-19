<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyVision extends Model
{
    protected $fillable = ['ceo_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class, 'ceo_id');
    }
}
