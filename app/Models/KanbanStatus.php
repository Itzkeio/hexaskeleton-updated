<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class KanbanStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kanban_statuses';

    /** BIARKAN DEFAULT (auto increment) */
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'projectId',
        'key',
        'label',
        'color_bg',
        'color_border',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /** Relasi ke Project */
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId', 'id');
    }

    /** Relasi ke Task */
    public function kanbans()
    {
        return $this->hasMany(Kanban::class, 'status', 'key');
    }

    /** Relasi ke Subtasks */
    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'status', 'key');
    }
}
