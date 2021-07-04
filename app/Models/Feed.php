<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;
    
    const FAIL = -1;
    const CHECK = 0;
    const SUCCESS = 1;
    const INVALID = 2;
}
