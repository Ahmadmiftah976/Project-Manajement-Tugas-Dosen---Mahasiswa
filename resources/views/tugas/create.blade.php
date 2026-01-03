@extends('layouts.app')

@section('title', 'Buat Tugas Baru')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kelas.show', $kelas->id) }}">{{ $kelas->mataKuliah->nama_mk }}</a></li>
            <li class="breadcrumb-item active">Buat Tugas Baru</li>
        </ol>
    </nav>
    <h1><i class="bi bi-plus-circle me-2"></i>Buat Tugas Baru</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Tugas Baru</h5>
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

                <form action="{{ route('tugas.store', $kelas->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" value="{{ old('judul') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi') }}</textarea>
                        <small class="text-muted">Jelaskan detail tugas, rubrik penilaian, dan instruksi pengerjaan</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipe" class="form-label">Tipe Tugas <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="individu" {{ old('tipe') == 'individu' ? 'selected' : '' }}>Individu</option>
                                <option value="kelompok" {{ old('tipe') == 'kelompok' ? 'selected' : '' }}>Kelompok</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bobot" class="form-label">Bobot Nilai <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="bobot" name="bobot" min="1" max="100" value="{{ old('bobot', 100) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="deadline" name="deadline" value="{{ old('deadline') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="file_lampiran" class="form-label">File Lampiran (Opsional)</label>
                        <input type="file" class="form-control" id="file_lampiran" name="file_lampiran">
                        <small class="text-muted">Upload file pendukung seperti template, rubrik, dll. (Max: 10MB)</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="allow_late_submission" name="allow_late_submission" value="1" {{ old('allow_late_submission') ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_late_submission">
                                Izinkan Pengumpulan Terlambat
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="penalty-section" style="display: none;">
                        <label for="late_penalty" class="form-label">Penalty Keterlambatan (%)</label>
                        <input type="number" class="form-control" id="late_penalty" name="late_penalty" min="0" max="100" value="{{ old('late_penalty', 0) }}">
                        <small class="text-muted">Pengurangan nilai dalam persen untuk submission terlambat</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-uin">
                            <i class="bi bi-save me-1"></i>Buat Tugas
                        </button>
                        <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary">
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
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Kelas</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Mata Kuliah:</strong></p>
                <p class="mb-3">{{ $kelas->mataKuliah->nama_mk }}</p>

                <p class="mb-2"><strong>Kelas:</strong></p>
                <p class="mb-3">{{ $kelas->nama_kelas }}</p>

                <p class="mb-2"><strong>Jumlah Mahasiswa:</strong></p>
                <p class="mb-3">{{ $kelas->enrollments->count() }} Mahasiswa</p>

                <p class="mb-2"><strong>Tahun Ajaran:</strong></p>
                <p class="mb-0">{{ $kelas->tahun_ajaran }} ({{ ucfirst($kelas->semester) }})</p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Berikan instruksi yang jelas dan detail</li>
                    <li>Sertakan rubrik penilaian jika memungkinkan</li>
                    <li>Set deadline yang realistis</li>
                    <li>Upload template jika diperlukan</li>
                    <li>Pertimbangkan penalty untuk keterlambatan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('allow_late_submission').addEventListener('change', function() {
        document.getElementById('penalty-section').style.display = this.checked ? 'block' : 'none';
    });

    // Show penalty section if checked on page load
    if(document.getElementById('allow_late_submission').checked) {
        document.getElementById('penalty-section').style.display = 'block';
    }
</script>
@endpush
@endsection