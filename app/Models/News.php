<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory, SoftDeletes;

    const FAIL = -1;
    const CHECK = 0;
    const SUCCESS = 1;

    protected $casts = [
        'cover' => 'array',
    ];

    public function feed()
    {
        return $this->hasOne('App\Models\Feed','id','feed_id');
    }
}
