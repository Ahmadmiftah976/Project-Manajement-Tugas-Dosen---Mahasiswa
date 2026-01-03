@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kelas.show', $tugas->kelas_id) }}">{{ $tugas->kelas->mataKuliah->nama_mk }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tugas.show', $tugas->id) }}">{{ $tugas->judul }}</a></li>
            <li class="breadcrumb-item active">Edit Tugas</li>
        </ol>
    </nav>
    <h1><i class="bi bi-pencil me-2"></i>Edit Tugas</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Edit Tugas</h5>
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

                <form action="{{ route('tugas.update', $tugas->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" 
                               value="{{ old('judul', $tugas->judul) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipe" class="form-label">Tipe Tugas <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="individu" {{ old('tipe', $tugas->tipe) == 'individu' ? 'selected' : '' }}>Individu</option>
                                <option value="kelompok" {{ old('tipe', $tugas->tipe) == 'kelompok' ? 'selected' : '' }}>Kelompok</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bobot" class="form-label">Bobot Nilai <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="bobot" name="bobot" 
                                   min="1" max="100" value="{{ old('bobot', $tugas->bobot) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="deadline" name="deadline" 
                               value="{{ old('deadline', $tugas->deadline->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="file_lampiran" class="form-label">File Lampiran</label>
                        @if($tugas->file_lampiran)
                            <div class="mb-2">
                                <small class="text-muted">File saat ini: 
                                    <a href="{{ Storage::url($tugas->file_lampiran) }}" target="_blank">
                                        {{ basename($tugas->file_lampiran) }}
                                    </a>
                                </small>
                            </div>
                        @endif
                        <input type="file" class="form-control" id="file_lampiran" name="file_lampiran">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah file (Max: 10MB)</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="allow_late_submission" 
                                   name="allow_late_submission" value="1" 
                                   {{ old('allow_late_submission', $tugas->allow_late_submission) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_late_submission">
                                Izinkan Pengumpulan Terlambat
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="penalty-section" style="display: {{ $tugas->allow_late_submission ? 'block' : 'none' }};">
                        <label for="late_penalty" class="form-label">Penalty Keterlambatan (%)</label>
                        <input type="number" class="form-control" id="late_penalty" name="late_penalty" 
                               min="0" max="100" value="{{ old('late_penalty', $tugas->late_penalty) }}">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status Tugas <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="draft" {{ old('status', $tugas->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $tugas->status) == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="closed" {{ old('status', $tugas->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <small class="text-muted">Draft = tidak tampil untuk mahasiswa, Published = aktif, Closed = tidak bisa submit</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-uin">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('tugas.show', $tugas->id) }}" class="btn btn-secondary">
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
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Tugas</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Mata Kuliah:</strong></p>
                <p class="mb-3">{{ $tugas->kelas->mataKuliah->nama_mk }}</p>

                <p class="mb-2"><strong>Kelas:</strong></p>
                <p class="mb-3">{{ $tugas->kelas->nama_kelas }}</p>

                <p class="mb-2"><strong>Total Submission:</strong></p>
                <p class="mb-3">{{ $tugas->submissions->count() }} mahasiswa</p>

                <p class="mb-2"><strong>Dibuat:</strong></p>
                <p class="mb-0">{{ $tugas->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Peringatan</h6>
            </div>
            <div class="card-body">
                <p class="small mb-0">
                    Perubahan pada tugas akan mempengaruhi semua mahasiswa yang sudah submit. Pastikan Anda mempertimbangkan dampaknya sebelum menyimpan.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('allow_late_submission').addEventListener('change', function() {
        document.getElementById('penalty-section').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endpush
@endsection