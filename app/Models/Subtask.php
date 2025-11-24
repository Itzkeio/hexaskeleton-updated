<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subtask extends Model
{
    protected $table = 'subtask';
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'kanbanId',
        'title',
        'description',
        'date_start',
        'date_end',
        'duration',
        'priority',
        'status',
    ];

    // PERBAIKAN: Hapus cast date, biarkan sebagai string
    protected $casts = [
        // Jangan cast ke date, biarkan sebagai string
        // 'date_start' => 'date',
        // 'date_end' => 'date',
    ];

    /** Auto-generate UUID */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /** Relasi ke Kanban */
    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'kanbanId', 'id');
    }
}