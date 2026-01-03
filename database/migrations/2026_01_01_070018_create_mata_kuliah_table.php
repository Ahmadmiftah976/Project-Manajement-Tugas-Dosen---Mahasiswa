<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mk')->unique();
            $table->string('nama_mk');
            $table->integer('sks');
            $table->string('semester');
            $table->string('prodi');
            $table->string('fakultas');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_kelas'); // Contoh: A, B, C
            $table->string('tahun_ajaran'); // Contoh: 2024/2025
            $table->enum('semester', ['ganjil', 'genap']);
            $table->string('ruangan')->nullable();
            $table->string('jadwal')->nullable(); // JSON format
            $table->integer('kapasitas')->default(40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('enrollment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['active', 'dropped', 'completed'])->default('active');
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['kelas_id', 'mahasiswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('mata_kuliah');
    }
};