<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class KanbanFile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kanban_files';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'id',
        'kanbanId',
        'subtaskId',
        'uploadedBy',
        'filename',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
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

    /** Relasi ke Kanban */
    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'kanbanId', 'id');
    }

    /** Relasi ke Subtask */
    public function subtask()
    {
        return $this->belongsTo(Subtask::class, 'subtaskId', 'id');
    }

    /** Relasi ke User */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploadedBy', 'id');
    }

    /** Helper: Format file size */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /** Helper: Get file icon based on type */
    public function getFileIconAttribute()
    {
        $type = $this->file_type;
        if (str_contains($type, 'image')) return 'ti-photo';
        if (str_contains($type, 'pdf')) return 'ti-file-type-pdf';
        if (str_contains($type, 'word') || str_contains($type, 'document')) return 'ti-file-type-doc';
        if (str_contains($type, 'excel') || str_contains($type, 'spreadsheet')) return 'ti-file-type-xls';
        if (str_contains($type, 'zip') || str_contains($type, 'compressed')) return 'ti-file-zip';
        return 'ti-file';
    }
}