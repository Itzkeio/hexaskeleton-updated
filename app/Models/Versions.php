<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Versions extends Model
{
    protected $table = 'versions';

    protected $fillable = [
        'projectId',
        'version',
        'description',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean', // otomatis convert 0/1 ke false/true
    ];
    
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId');
    }
}
