<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use HasFactory;

    protected $table = 'timeline'; // 💡 Gunakan plural (konvensi Laravel)

    protected $fillable = [
        'projectId',
        'title',
        'description',
        'start_date',
        'end_date',
        'progress',
        'type',   // untuk persentase progress
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'integer',
    ];

    // Relasi ke project
    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectId');
    }

     public function getDurationInDays()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
    }

    /**
     * Helper: Cek apakah timeline sudah lewat
     */
    public function isOverdue()
    {
        if (!$this->end_date) {
            return false;
        }
        
        return Carbon::parse($this->end_date)->isPast() && $this->progress < 100;
    }

    /**
     * Helper: Dapatkan status warna
     */
    public function getStatusColor()
    {
        if ($this->progress == 100) {
            return 'success';
        } elseif ($this->isOverdue()) {
            return 'danger';
        } elseif ($this->progress >= 50) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by project
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('projectId', $projectId);
    }
}
