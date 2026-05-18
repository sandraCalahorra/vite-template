<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'project_id',
        'file_path',
        'warning',
        'ticket_id',
        'user_id'
    ];
    protected $table = "files";

}
