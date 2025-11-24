<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kanban extends Model
{
    use HasFactory;

    protected $table = 'kanban';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'projectId',
        'title',
        'description',
        'priority',
        'status',
        'date_start',
        'date_end',
        'duration',
    ];

    /** Relasi ke Project */
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId', 'id');
    }

    /** Relasi ke Subtasks */
    public function subtasks()
{
    return $this->hasMany(Subtask::class, 'kanbanId', 'id');
}
}
