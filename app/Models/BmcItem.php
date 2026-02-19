<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BmcItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ceo_id',
        'block',
        'content',
        'color'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ceo_id');
    }
}
