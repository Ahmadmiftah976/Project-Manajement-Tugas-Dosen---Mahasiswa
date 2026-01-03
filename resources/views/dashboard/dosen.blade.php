@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-grid-fill me-2"></i>Dashboard Dosen</h1>
    <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h3>{{ $stats['total_kelas'] }}</h3>
            <p><i class="bi bi-book me-2"></i>Kelas Diampu</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h3>{{ $stats['total_tugas'] }}</h3>
            <p><i class="bi bi-clipboard-check me-2"></i>Total Tugas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h3>{{ $stats['total_mahasiswa'] }}</h3>
            <p><i class="bi bi-people me-2"></i>Total Mahasiswa</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <h3>{{ $stats['pending_grading'] }}</h3>
            <p><i class="bi bi-exclamation-triangle me-2"></i>Perlu Dinilai</p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row mb-4">
    <!-- Kelas yang Diampu -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-book-fill me-2"></i>Kelas yang Diampu</span>
                <a href="{{ route('kelas.create') }}" class="btn btn-sm btn-uin">
                    <i class="bi bi-plus-circle me-1"></i>Buat Kelas Baru
                </a>
            </div>
            <div class="card-body">
                @forelse($kelas as $k)
                    <div class="card mb-3 border-start border-4" style="border-left-color: var(--uin-green) !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $k->mataKuliah->nama_mk }}</h6>
                                    <p class="text-muted mb-2 small">
                                        <i class="bi bi-door-open me-1"></i>Kelas {{ $k->nama_kelas }} | 
                                        <i class="bi bi-calendar me-1"></i>{{ $k->tahun_ajaran }} ({{ ucfirst($k->semester) }})
                                    </p>
                                    <span class="badge bg-primary">{{ $k->enrollments->count() }} Mahasiswa</span>
                                </div>
                                <div>
                                    <a href="{{ route('kelas.show', $k->id) }}" class="btn btn-sm btn-uin me-2">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('tugas.create', $k->id) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus"></i> Tugas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Belum ada kelas</p>
                        <a href="{{ route('kelas.create') }}" class="btn btn-uin">
                            <i class="bi bi-plus-circle me-1"></i>Buat Kelas Pertama
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell me-2"></i>Notifikasi</span>
                <span class="badge bg-danger">{{ $notifications->count() }}</span>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($notifications as $notification)
                    <div class="alert alert-light border-start border-3 border-info mb-2">
                        <h6 class="alert-heading mb-1 small">{{ $notification->title }}</h6>
                        <p class="mb-1 small">{{ $notification->message }}</p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-bell-slash display-6"></i>
                        <p class="mt-2 small">Tidak ada notifikasi baru</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Tugas Terbaru -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clipboard-check me-2"></i>Tugas Terbaru</span>
                <a href="{{ route('dosen.tugas') }}" class="btn btn-sm btn-outline-success">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul Tugas</th>
                                <th>Mata Kuliah</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugas as $t)
                                <tr>
                                    <td>{{ $t->judul }}</td>
                                    <td>{{ $t->kelas->mataKuliah->nama_mk }}</td>
                                    <td>{{ $t->deadline->format('d M Y, H:i') }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($t->status == 'published') bg-success
                                            @elseif($t->status == 'draft') bg-warning
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($t->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-sm btn-uin">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada tugas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submission Perlu Dinilai -->
<div class="row" id="submissions">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-inbox-fill me-2"></i>Submission Perlu Dinilai</span>
                <span class="badge bg-warning text-dark">{{ $pendingSubmissions->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Tugas</th>
                                <th>Mata Kuliah</th>
                                <th>Waktu Submit</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingSubmissions as $submission)
                                <tr>
                                    <td>
                                        <strong>{{ $submission->mahasiswa->name }}</strong><br>
                                        <small class="text-muted">{{ $submission->mahasiswa->nim_nip }}</small>
                                    </td>
                                    <td>{{ $submission->tugas->judul }}</td>
                                    <td>{{ $submission->tugas->kelas->mataKuliah->nama_mk }}</td>
                                    <td>
                                        {{ $submission->submitted_at->format('d M Y, H:i') }}
                                        @if($submission->is_late)
                                            <br><span class="badge bg-danger">Terlambat</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Pending</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('tugas.show', $submission->tugas_id) }}" class="btn btn-sm btn-uin">
                                            <i class="bi bi-eye me-1"></i>Nilai
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Tidak ada submission yang perlu dinilai
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection