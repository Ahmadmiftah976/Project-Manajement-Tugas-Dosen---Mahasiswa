<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'kelas_id',
        'judul',
        'deskripsi',
        'tipe',
        'deadline',
        'bobot',
        'file_lampiran',
        'allow_late_submission',
        'late_penalty',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'allow_late_submission' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function isOverdue()
    {
        return Carbon::now()->isAfter($this->deadline);
    }

    public function daysUntilDeadline()
    {
        return Carbon::now()->diffInDays($this->deadline, false);
    }
}