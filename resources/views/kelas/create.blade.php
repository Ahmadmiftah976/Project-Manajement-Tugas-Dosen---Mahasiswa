@extends('layouts.app')

@section('title', 'Buat Kelas Baru')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('dosen.classes') }}">Kelas Saya</a></li>
            <li class="breadcrumb-item active">Buat Kelas Baru</li>
        </ol>
    </nav>
    <h1><i class="bi bi-plus-circle me-2"></i>Buat Kelas Baru</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Kelas Baru</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="mata_kuliah_id" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                        <select class="form-select" id="mata_kuliah_id" name="mata_kuliah_id" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mataKuliah as $mk)
                                <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                    {{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" 
                                   placeholder="Contoh: A, B, C" value="{{ old('nama_kelas') }}" required>
                            <small class="text-muted">Biasanya berupa huruf (A, B, C) atau angka (1, 2, 3)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="kapasitas" class="form-label">Kapasitas Mahasiswa <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                                   min="1" max="100" value="{{ old('kapasitas', 40) }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" 
                                   placeholder="2024/2025" value="{{ old('tahun_ajaran', '2024/2025') }}" required>
                            <small class="text-muted">Format: YYYY/YYYY</small>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="ganjil" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" {{ old('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ruangan" class="form-label">Ruangan (Opsional)</label>
                        <input type="text" class="form-control" id="ruangan" name="ruangan" 
                               placeholder="Contoh: Lab. Komputer 1, Ruang 305" value="{{ old('ruangan') }}">
                    </div>

                    <div class="mb-3">
                        <label for="jadwal" class="form-label">Jadwal (Opsional)</label>
                        <input type="text" class="form-control" id="jadwal" name="jadwal" 
                               placeholder="Contoh: Senin 08:00-10:30, Rabu 13:00-15:30" value="{{ old('jadwal') }}">
                        <small class="text-muted">Tuliskan jadwal pertemuan kelas</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-uin">
                            <i class="bi bi-save me-1"></i>Buat Kelas
                        </button>
                        <a href="{{ route('dosen.classes') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <p class="small mb-3">
                    <strong>Mata Kuliah:</strong> Pilih mata kuliah yang akan Anda ajar dari daftar yang tersedia.
                </p>
                <p class="small mb-3">
                    <strong>Nama Kelas:</strong> Identifikasi kelas (biasanya huruf atau angka).
                </p>
                <p class="small mb-3">
                    <strong>Kapasitas:</strong> Jumlah maksimal mahasiswa yang dapat mendaftar.
                </p>
                <p class="small mb-0">
                    <strong>Status:</strong> Kelas akan otomatis aktif setelah dibuat.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Pastikan mata kuliah sudah terdaftar di sistem</li>
                    <li>Gunakan nama kelas yang konsisten (A, B, C)</li>
                    <li>Sesuaikan kapasitas dengan ruangan</li>
                    <li>Tambahkan jadwal agar mahasiswa tahu waktu kuliah</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection