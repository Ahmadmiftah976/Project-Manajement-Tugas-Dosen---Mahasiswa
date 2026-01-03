<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'mata_kuliah_id',
        'dosen_id',
        'nama_kelas',
        'tahun_ajaran',
        'semester',
        'ruangan',
        'jadwal',
        'kapasitas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function mahasiswa()
    {
        return $this->belongsToMany(User::class, 'enrollment', 'kelas_id', 'mahasiswa_id')
                    ->withPivot('status', 'nilai_akhir')
                    ->withTimestamps();
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }
}