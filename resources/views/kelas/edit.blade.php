@extends('layouts.app')

@section('title', 'Edit Kelas')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('dosen.classes') }}">Kelas Saya</a></li>
            <li class="breadcrumb-item active">Edit Kelas</li>
        </ol>
    </nav>
    <h1><i class="bi bi-pencil me-2"></i>Edit Kelas</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Edit Kelas</h5>
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

                <form action="{{ route('kelas.update', $kelas->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="mata_kuliah_id" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                        <select class="form-select" id="mata_kuliah_id" name="mata_kuliah_id" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mataKuliah as $mk)
                                <option value="{{ $mk->id }}" {{ (old('mata_kuliah_id', $kelas->mata_kuliah_id) == $mk->id) ? 'selected' : '' }}>
                                    {{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" 
                                   value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="kapasitas" class="form-label">Kapasitas Mahasiswa <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                                   min="1" max="100" value="{{ old('kapasitas', $kelas->kapasitas) }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" 
                                   value="{{ old('tahun_ajaran', $kelas->tahun_ajaran) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="ganjil" {{ old('semester', $kelas->semester) == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" {{ old('semester', $kelas->semester) == 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ruangan" class="form-label">Ruangan</label>
                        <input type="text" class="form-control" id="ruangan" name="ruangan" 
                               value="{{ old('ruangan', $kelas->ruangan) }}">
                    </div>

                    <div class="mb-3">
                        <label for="jadwal" class="form-label">Jadwal</label>
                        <input type="text" class="form-control" id="jadwal" name="jadwal" 
                               value="{{ old('jadwal', $kelas->jadwal) }}">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $kelas->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Kelas Aktif
                            </label>
                        </div>
                        <small class="text-muted">Nonaktifkan jika kelas sudah tidak digunakan</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-uin">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                        <button type="button" class="btn btn-danger ms-auto" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i>Hapus Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Kelas</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Total Mahasiswa:</strong></p>
                <p class="mb-3">{{ $kelas->enrollments->count() }} / {{ $kelas->kapasitas }} mahasiswa</p>

                <p class="mb-2"><strong>Total Tugas:</strong></p>
                <p class="mb-3">{{ $kelas->tugas->count() }} tugas</p>

                <p class="mb-2"><strong>Status:</strong></p>
                <p class="mb-0">
                    <span class="badge {{ $kelas->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $kelas->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Peringatan</h6>
            </div>
            <div class="card-body">
                <p class="small text-danger mb-0">
                    <strong>Hati-hati!</strong> Menghapus kelas akan menghapus semua data terkait termasuk tugas dan submission mahasiswa. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus Kelas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kelas ini?</p>
                <p class="text-danger"><strong>Peringatan:</strong> Semua tugas dan submission akan ikut terhapus!</p>
                <p class="mb-0"><strong>Kelas:</strong> {{ $kelas->mataKuliah->nama_mk }} - Kelas {{ $kelas->nama_kelas }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('kelas.destroy', $kelas->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus Kelas</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
