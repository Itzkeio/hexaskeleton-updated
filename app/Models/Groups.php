<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'name',
        'userId'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}