<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kanban extends Model
{
    use HasFactory;

    protected $table = 'kanban';

    protected $fillable = [
        'id',
        'projectId',
        'title',
        'date_start',
        'date_end',
        'duration',
        'description',
        'priority',
        'status',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId');
    }
}

