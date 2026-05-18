<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionsActionsType extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public $table = "permissions_actions_types";

}
