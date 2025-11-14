<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'userRoles';

    protected $fillable = [
        'userId',
        'roleId',
        'createdAt',
        'updatedAt'
    ];

    // Disable timestamps
    public $timestamps = false;
}
