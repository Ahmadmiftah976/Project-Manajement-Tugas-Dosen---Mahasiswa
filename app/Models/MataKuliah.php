<?php

// App/Models/MataKuliah.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'sks',
        'semester',
        'prodi',
        'fakultas',
        'deskripsi',
    ];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}