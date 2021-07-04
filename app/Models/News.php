<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    const FAIL = -1;
    const CHECK = 0;
    const SUCCESS = 1;
}
