<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

     protected $table = 'users';

    protected $fillable = [
        'compName',
        'nik',
        'name',
        'divName',
        'jobLvlName',
        'jobTtlName',
        'role'
    ];

    // Disable timestamps
    public $timestamps = false;
}
