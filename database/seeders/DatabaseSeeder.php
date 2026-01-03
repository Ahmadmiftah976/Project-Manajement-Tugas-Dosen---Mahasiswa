<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'nim_nip' => 'ADM001',
            'name' => 'Administrator',
            'email' => 'admin@uin-alauddin.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'fakultas' => 'Sistem Informasi',
            'is_active' => true,
        ]);

        // Create Dosen
        $dosen1 = User::create([
            'nim_nip' => '197001011998031001',
            'name' => 'Dr. Ahmad Yani, M.Kom',
            'email' => 'ahmad.yani@uin-alauddin.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $dosen2 = User::create([
            'nim_nip' => '198505152010121002',
            'name' => 'Dr. Siti Nurhaliza, M.T',
            'email' => 'siti.nurhaliza@uin-alauddin.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        // Create Mahasiswa
        $mahasiswa1 = User::create([
            'nim_nip' => '60200121001',
            'name' => 'Muhammad Rizki',
            'email' => 'muhammad.rizki@uin-alauddin.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        $mahasiswa2 = User::create([
            'nim_nip' => '60200121002',
            'name' => 'Fatimah Azzahra',
            'email' => 'fatimah.azzahra@uin-alauddin.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        $mahasiswa3 = User::create([
            'nim_nip' => '60200121003',
            'name' => 'Abdullah Rahman',
            'email' => 'abdullah.rahman@uin-alauddin.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'phone' => '081234567894',
            'is_active' => true,
        ]);

        // Create Mata Kuliah
        $mk1 = MataKuliah::create([
            'kode_mk' => 'SI301',
            'nama_mk' => 'Pemrograman Web',
            'sks' => 3,
            'semester' => '5',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'deskripsi' => 'Mata kuliah yang mempelajari pengembangan aplikasi web',
        ]);

        $mk2 = MataKuliah::create([
            'kode_mk' => 'SI302',
            'nama_mk' => 'Basis Data Lanjut',
            'sks' => 3,
            'semester' => '5',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'deskripsi' => 'Mata kuliah yang mempelajari konsep basis data tingkat lanjut',
        ]);

        $mk3 = MataKuliah::create([
            'kode_mk' => 'SI303',
            'nama_mk' => 'Rekayasa Perangkat Lunak',
            'sks' => 3,
            'semester' => '5',
            'prodi' => 'Sistem Informasi',
            'fakultas' => 'Sains dan Teknologi',
            'deskripsi' => 'Mata kuliah yang mempelajari metodologi pengembangan perangkat lunak',
        ]);

        // Create Kelas
        $kelas1 = Kelas::create([
            'mata_kuliah_id' => $mk1->id,
            'dosen_id' => $dosen1->id,
            'nama_kelas' => 'A',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'ganjil',
            'ruangan' => 'Lab. Komputer 1',
            'jadwal' => json_encode(['hari' => 'Senin', 'jam' => '08:00-10:30']),
            'kapasitas' => 40,
            'is_active' => true,
        ]);

        $kelas2 = Kelas::create([
            'mata_kuliah_id' => $mk2->id,
            'dosen_id' => $dosen2->id,
            'nama_kelas' => 'A',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'ganjil',
            'ruangan' => 'Lab. Komputer 2',
            'jadwal' => json_encode(['hari' => 'Rabu', 'jam' => '13:00-15:30']),
            'kapasitas' => 40,
            'is_active' => true,
        ]);

        $kelas3 = Kelas::create([
            'mata_kuliah_id' => $mk3->id,
            'dosen_id' => $dosen1->id,
            'nama_kelas' => 'A',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'ganjil',
            'ruangan' => 'Ruang 305',
            'jadwal' => json_encode(['hari' => 'Jumat', 'jam' => '08:00-10:30']),
            'kapasitas' => 40,
            'is_active' => true,
        ]);

        // Create Enrollment
        Enrollment::create([
            'kelas_id' => $kelas1->id,
            'mahasiswa_id' => $mahasiswa1->id,
            'status' => 'active',
        ]);

        Enrollment::create([
            'kelas_id' => $kelas1->id,
            'mahasiswa_id' => $mahasiswa2->id,
            'status' => 'active',
        ]);

        Enrollment::create([
            'kelas_id' => $kelas1->id,
            'mahasiswa_id' => $mahasiswa3->id,
            'status' => 'active',
        ]);

        Enrollment::create([
            'kelas_id' => $kelas2->id,
            'mahasiswa_id' => $mahasiswa1->id,
            'status' => 'active',
        ]);

        Enrollment::create([
            'kelas_id' => $kelas2->id,
            'mahasiswa_id' => $mahasiswa2->id,
            'status' => 'active',
        ]);

        Enrollment::create([
            'kelas_id' => $kelas3->id,
            'mahasiswa_id' => $mahasiswa1->id,
            'status' => 'active',
        ]);

        Enrollment::create([
            'kelas_id' => $kelas3->id,
            'mahasiswa_id' => $mahasiswa3->id,
            'status' => 'active',
        ]);
    }
}