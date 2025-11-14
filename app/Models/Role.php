<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'compCode',
        'compName',
        'name',
        'description',
        'createdAt',
        'updatedAt'
    ];

    // Disable timestamps
    public $timestamps = false;

    public function rbac()
    {
        return $this->hasMany(RoleRbac::class, 'roleId');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
