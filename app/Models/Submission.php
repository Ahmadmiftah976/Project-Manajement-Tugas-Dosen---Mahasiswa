<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'submission';

    protected $fillable = [
        'tugas_id',
        'mahasiswa_id',
        'catatan',
        'file_submission',
        'submitted_at',
        'is_late',
        'status_revisi',
        'nilai',
        'attempt',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function komentar()
    {
        return $this->hasMany(Komentar::class);
    }
}