<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCoreValue extends Model
{
    protected $fillable = ['ceo_id', 'title', 'description', 'order'];

    public function user()
    {
        return $this->belongsTo(User::class, 'ceo_id');
    }
}
