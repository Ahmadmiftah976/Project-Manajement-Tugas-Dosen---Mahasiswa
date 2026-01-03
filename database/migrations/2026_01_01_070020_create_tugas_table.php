<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('tipe', ['individu', 'kelompok']);
            $table->dateTime('deadline');
            $table->integer('bobot')->default(100); // Nilai maksimal
            $table->string('file_lampiran')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->integer('late_penalty')->default(0); // Persentase pengurangan
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->timestamps();
        });

        Schema::create('submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->string('file_submission');
            $table->dateTime('submitted_at');
            $table->boolean('is_late')->default(false);
            $table->enum('status_revisi', ['pending', 'diterima', 'revisi', 'ditolak'])->default('pending');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->integer('attempt')->default(1); // Untuk tracking revisi
            $table->timestamps();
            
            $table->unique(['tugas_id', 'mahasiswa_id', 'attempt']);
        });

        Schema::create('komentar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submission')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('komentar');
            $table->string('file_lampiran')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('type'); // deadline, submission, comment, grade
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('komentar');
        Schema::dropIfExists('submission');
        Schema::dropIfExists('tugas');
    }
};