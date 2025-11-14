<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Projects extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'picId',
        'picType',
        'versionId',
        'createdAt',
        'updatedAt',
        'finishedAt',
        'dampak'
    ];

    // Disable timestamps (karena pakai createdAt dan finishedAt manual)
    public $timestamps = false;

    /**
     * Relasi ke version AKTIF (singular)
     */
    public function version()
    {
        return $this->belongsTo(Versions::class, 'versionId');
    }

    /**
     * Relasi ke SEMUA versions (riwayat)
     */
    public function versions()
    {
        return $this->hasMany(Versions::class, 'projectId')->orderBy('created_at', 'desc');
    }

    /**
     * ✅ Relasi ke banyak timeline actual plan
     */
    public function timeline()
    {
        return $this->hasMany(Timeline::class, 'projectId');
    }

     public function actualTimelines()
    {
        return $this->hasMany(Timeline::class, 'projectId')
            ->where('type', 'actual')
            ->orderBy('start_date', 'asc');
    }
    /**
     * PIC bisa ke users atau groups tergantung picType
     */
    public function pic()
    {
        if ($this->picType === 'individual') {
            return $this->belongsTo(User::class, 'picId');
        } else {
            return $this->belongsTo(Groups::class, 'picId');
        }
    }

    /**
     * Helper method untuk mendapatkan version aktif
     */
    public function getActiveVersion()
    {
        return $this->versions()->where('status', true)->first();
    }

    /**
     * Helper method untuk cek apakah project punya version aktif
     */
    public function hasActiveVersion()
    {
        return $this->versions()->where('status', true)->exists();
    }

    /**
     * 🔹 Helper tambahan: ambil plan awal (dari tanggal project)
     */
    public function getPlanAwal()
    {
        return [
           'title' => 'Plan Awal: ' . $this->name,
            'start' => $this->createdAt,
            'end' => $this->finishedAt ?? now(),
            'type' => 'plan'
        ];
    }
    public function getOverallProgress()
    {
        $actualTimelines = $this->actualTimelines;
        
        if ($actualTimelines->count() === 0) {
            return 0;
        }
        
        $totalProgress = $actualTimelines->sum('progress');
        return round($totalProgress / $actualTimelines->count());
    }
}
