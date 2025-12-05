<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subtask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subtask';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'kanbanId',
        'title',
        'description',
        'priority',
        'notes',
        'status',
        'date_start',
        'date_end',
        'duration',
    ];

    protected $dates = ['deleted_at'];

    /** Relasi ke Kanban */
    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'kanbanId', 'id');
    }

    /** Relasi ke Files */
    public function files()
    {
        return $this->hasMany(KanbanFile::class, 'subtaskId', 'id')
            ->whereNull('deleted_at');
    }
}