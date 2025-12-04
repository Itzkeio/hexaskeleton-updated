<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class KanbanLog extends Model
{
    use HasFactory;

    protected $table = 'kanban_logs';

    protected $fillable = [
        'userId',
        'kanbanId',
        'subtaskId',
        'projectId',
        'action',
        'entity_type',
        'description',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
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

    // -----------------------------
    // RELATIONS
    // -----------------------------

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'kanbanId', 'id');
    }

    public function subtask()
    {
        return $this->belongsTo(Subtask::class, 'subtaskId', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId', 'id');
    }

    // -----------------------------
    // CREATE LOG HELPER
    // -----------------------------
    public static function createLog($data)
    {
        return self::create([
            'userId'      => $data['userId']      ?? auth()->id(),
            'kanbanId'    => $data['kanbanId']    ?? null,
            'subtaskId'   => $data['subtaskId']   ?? null,
            'projectId'   => $data['projectId'],  // WAJIB
            'action'      => $data['action'],
            'entity_type' => $data['entity_type'],
            'description' => $data['description'],
            'old_values'  => $data['old_values']  ?? null,
            'new_values'  => $data['new_values']  ?? null,
        ]);
    }
}
