<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleRbac extends Model
{
    use HasFactory;

    protected $table = 'roleRbac';

    protected $fillable = [
        'roleId',
        'keyname'
    ];

    // Disable timestamps
    public $timestamps = false;
}
