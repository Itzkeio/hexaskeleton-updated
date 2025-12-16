<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use HasFactory;

    protected $table = 'timeline';

    protected $fillable = [
        'projectId',
        'title',
        'description',
        'start_date',
        'end_date',
        'progress',
        'type',
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
     * ✅ FIXED: Cek apakah timeline overdue dengan logic yang BENAR
     */
    public function isOverdue()
    {
        // 1. Jika progress sudah 100%, tidak mungkin overdue
        if ($this->progress >= 100) {
            return false;
        }

        // 2. Jika tidak ada end_date, tidak bisa dikategorikan overdue
        if (!$this->end_date) {
            return false;
        }

        // 3. Jika ini adalah PLAN AWAL (type = 'plan'), tidak pernah overdue
        if ($this->type === 'plan') {
            return false;
        }

        // 4. Untuk ACTUAL TIMELINE, cek hari ini vs end_date actual
        $today = Carbon::today();
        $actualEndDate = Carbon::parse($this->end_date);

        // ✅ LOGIC UTAMA: 
        // Overdue HANYA jika:
        // - Hari ini SUDAH MELEWATI end_date actual
        // - DAN progress masih < 100%
        
        // NOTE: Tidak peduli apakah end_date dalam atau di luar range plan awal
        // Yang penting adalah: "Apakah sudah lewat deadline yang ditetapkan di timeline actual ini?"
        
        return $today->gt($actualEndDate) && $this->progress < 100;
    }

    /**
     * ✅ Helper: Cek apakah timeline melewati deadline plan awal
     * (Ini untuk WARNING, bukan OVERDUE)
     */
    public function isExceedingPlanDeadline()
    {
        if ($this->type !== 'actual' || !$this->end_date) {
            return false;
        }

        $project = $this->project;
        
        if (!$project || !$project->finishedAt) {
            return false;
        }

        $planEndDate = Carbon::parse($project->finishedAt);
        $actualEndDate = Carbon::parse($this->end_date);

        // Melewati plan deadline jika end_date actual > plan end date
        return $actualEndDate->gt($planEndDate);
    }

    /**
     * ✅ Helper: Cek apakah timeline masih dalam range plan awal
     */
    public function isWithinPlanRange()
    {
        if ($this->type !== 'actual' || !$this->start_date || !$this->end_date) {
            return false;
        }

        $project = $this->project;
        
        if (!$project || !$project->createdAt || !$project->finishedAt) {
            return false;
        }

        $planStartDate = Carbon::parse($project->createdAt);
        $planEndDate = Carbon::parse($project->finishedAt);
        $actualStartDate = Carbon::parse($this->start_date);
        $actualEndDate = Carbon::parse($this->end_date);

        // Dalam range jika kedua tanggal (start & end) masih dalam plan
        return $actualStartDate->between($planStartDate, $planEndDate) 
               && $actualEndDate->between($planStartDate, $planEndDate);
    }

    /**
     * ✅ Helper: Dapatkan status warna (updated)
     */
    public function getStatusColor()
    {
        // Progress 100% = hijau (selesai)
        if ($this->progress >= 100) {
            return 'success';
        }
        
        // Overdue = merah (sudah lewat deadline actual)
        if ($this->isOverdue()) {
            return 'danger';
        }
        
        // Melewati deadline plan awal (tapi belum overdue) = orange
        if ($this->isExceedingPlanDeadline()) {
            return 'warning';
        }
        
        // Progress tinggi = hijau muda
        if ($this->progress >= 75) {
            return 'success';
        }
        
        // Progress medium = kuning
        if ($this->progress >= 50) {
            return 'warning';
        }
        
        // Progress rendah = biru
        if ($this->progress >= 25) {
            return 'info';
        }
        
        // Progress sangat rendah = secondary
        return 'secondary';
    }

    /**
     * ✅ Helper: Get status text untuk display
     */
    public function getStatusText()
    {
        if ($this->progress >= 100) {
            return 'Selesai';
        }
        
        if ($this->isOverdue()) {
            $today = Carbon::today();
            $endDate = Carbon::parse($this->end_date);
            $daysLate = $today->diffInDays($endDate);
            return "Terlambat {$daysLate} hari";
        }
        
        if ($this->isExceedingPlanDeadline()) {
            return 'Melewati Plan Awal';
        }
        
        $today = Carbon::today();
        $endDate = Carbon::parse($this->end_date);
        $daysLeft = $endDate->diffInDays($today);
        
        if ($endDate->isFuture()) {
            return "{$daysLeft} hari lagi";
        } elseif ($endDate->isToday()) {
            return 'Deadline hari ini';
        } else {
            return "{$daysLeft} hari terlambat";
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