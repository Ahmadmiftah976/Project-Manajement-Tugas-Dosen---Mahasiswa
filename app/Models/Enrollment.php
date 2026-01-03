<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollment';

    protected $fillable = [
        'kelas_id',
        'mahasiswa_id',
        'status',
        'nilai_akhir',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
}