<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Groups extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'id',
        'name',
        'userId',
        'projectId'   // ← WAJIB ADA
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->id)) {
                $group->id = Str::uuid()->toString();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    // GROUP → MEMBERS
    public function members()
{
    return $this->belongsToMany(
        User::class,
        'group_members',
        'group_id',
        'user_id'
    );
}


    // GROUP → PROJECT
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId');
    }
}
