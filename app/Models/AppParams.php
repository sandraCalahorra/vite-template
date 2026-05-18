<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppParams extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
    ];

}
