<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class KanbanStatus extends Model
{
    use HasFactory;

    protected $table = 'kanban_status';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'projectId',
        'name',
        'color',
        'order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /** Relasi ke Project */
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId', 'id');
    }

    /** Relasi ke Kanban tasks */
    public function kanbans()
    {
        return $this->hasMany(Kanban::class, 'status', 'id');
    }

    /** Relasi ke Subtasks */
    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'status', 'id');
    }
}