<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Kanban extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kanban';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $appends = ['pic', 'pic_name'];

    protected $fillable = [
        'id',
        'projectId',
        'title',
        'description',
        'notes',
        'priority',
        'picType',
        'picId',
        'status',
        'date_start',
        'date_end',
        'duration',
    ];


    protected $dates = ['deleted_at'];

    /** Relasi ke Project */
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId', 'id');
    }

    /** Relasi ke Subtasks */
    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'kanbanId', 'id')
            ->whereNull('deleted_at');
    }

    /** Relasi ke Files */
     public function files()
    {
        return $this->hasMany(KanbanFile::class, 'kanbanId', 'id')
                    ->whereNull('subtaskId')  // ⚠️ PENTING: Filter subtask files
                    ->whereNull('deleted_at');
    }

// public function pic()
// {
//     // Validasi STRICT di awal
//     if ($this->picType === 'individual') {
//         if (!empty($this->picId) && Str::isUuid($this->picId)) {
//             return $this->belongsTo(\App\Models\User::class, 'picId', 'id');
//         }
        
//         // ✅ Return empty BelongsTo tanpa execute query
//         return $this->belongsTo(\App\Models\User::class, 'picId', 'id')
//             ->where('users.id', '!=', 'users.id'); // Trick: Always false
//     }

//     // Group
//     if (!empty($this->picId) && Str::isUuid($this->picId)) {
//         return $this->belongsTo(\App\Models\Groups::class, 'picId', 'id');
//     }

//     // ✅ Return empty BelongsTo tanpa execute query
//     return $this->belongsTo(\App\Models\Groups::class, 'picId', 'id')
//         ->where('groups.id', '!=', 'groups.id'); // Trick: Always false
// }

    /**
     * ✅ HELPER: Get PIC name safely
     */
    public function getPicAttribute()
    {
        if (empty($this->picId) || !isset($this->picType)) {
            return null;
        }

        // Cache result
        if (!isset($this->relations['pic'])) {
            if ($this->picType === 'individual' && Str::isUuid($this->picId)) {
                $this->setRelation('pic', \App\Models\User::find($this->picId));
            } elseif ($this->picType === 'group' && Str::isUuid($this->picId)) {
                $this->setRelation('pic', \App\Models\Groups::find($this->picId));
            } else {
                $this->setRelation('pic', null);
            }
        }

        return $this->relations['pic'];
    }

    /**
     * Get nama PIC
     */
    public function getPicNameAttribute()
    {
        $pic = $this->pic;
        return $pic ? $pic->name : '-';
    }

    public function statusInfo()
    {
        return $this->belongsTo(KanbanStatus::class, 'status', 'key');
    }
}
