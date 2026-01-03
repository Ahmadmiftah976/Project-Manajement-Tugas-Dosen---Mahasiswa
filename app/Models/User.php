<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nim_nip',
        'name',
        'email',
        'password',
        'role',
        'prodi',
        'fakultas',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function kelasAsDoson()
    {
        return $this->hasMany(Kelas::class, 'dosen_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'mahasiswa_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'mahasiswa_id');
    }

    public function komentar()
    {
        return $this->hasMany(Komentar::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false);
    }

    // Helper methods
    public function isDosen()
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa()
    {
        return $this->role === 'mahasiswa';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}