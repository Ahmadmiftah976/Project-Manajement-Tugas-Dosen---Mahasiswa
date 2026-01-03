@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-grid-fill me-2"></i>Dashboard Mahasiswa</h1>
    <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h3>{{ $stats['total_kelas'] }}</h3>
            <p><i class="bi bi-book me-2"></i>Mata Kuliah</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h3>{{ $stats['total_tugas'] }}</h3>
            <p><i class="bi bi-clipboard-check me-2"></i>Total Tugas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <h3>{{ $stats['tugas_selesai'] }}</h3>
            <p><i class="bi bi-check-circle me-2"></i>Tugas Selesai</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-danger">
            <h3>{{ $stats['tugas_pending'] }}</h3>
            <p><i class="bi bi-exclamation-triangle me-2"></i>Tugas Tertunda</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Tugas Mendatang</span>
                <span class="badge bg-primary">{{ $upcomingTugas->count() }}</span>
            </div>
            <div class="card-body">
                @forelse($upcomingTugas as $tugas)
                    @php
                        // Logika warna background (Class) tetap menggunakan hitungan hari
                        $daysLeftInt = now()->diffInDays($tugas->deadline, false);
                        $deadlineClass = $daysLeftInt <= 1 ? 'deadline-urgent' : ($daysLeftInt <= 3 ? 'deadline-soon' : 'deadline-normal');
                    @endphp
                    <div class="card mb-3 {{ $deadlineClass }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $tugas->judul }}</h6>
                                    <p class="text-muted mb-2 small">
                                        <i class="bi bi-book me-1"></i>{{ $tugas->kelas->mataKuliah->nama_mk }}
                                    </p>
                                    <p class="mb-0 small">
                                        <i class="bi bi-clock me-1"></i>
                                        Deadline: {{ $tugas->deadline->format('d M Y, H:i') }}
                                        
                                        {{-- PERBAIKAN DI SINI: Gunakan diffForHumans agar rapi --}}
                                        @if($tugas->deadline->isFuture())
                                            <span class="badge bg-warning text-dark ms-2">
                                                {{ $tugas->deadline->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger ms-2">
                                                Terlambat {{ $tugas->deadline->diffForHumans(null, true) }}
                                            </span>
                                        @endif
                                        {{-- AKHIR PERBAIKAN --}}
                                    </p>
                                </div>
                                <a href="{{ route('tugas.show', $tugas->id) }}" class="btn btn-sm btn-uin">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada tugas mendatang</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

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

@if($overdueTugas->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Tugas Terlambat
            </div>
            <div class="card-body">
                @foreach($overdueTugas as $tugas)
                    <div class="alert alert-danger d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $tugas->judul }}</strong>
                            <p class="mb-0 small">{{ $tugas->kelas->mataKuliah->nama_mk }}</p>
                            <small>Deadline: {{ $tugas->deadline->format('d M Y, H:i') }}</small>
                        </div>
                        <a href="{{ route('tugas.show', $tugas->id) }}" class="btn btn-danger btn-sm">
                            Kumpulkan Sekarang
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<div class="row" id="kelas">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-book-fill me-2"></i>Mata Kuliah Saya
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($enrollments as $enrollment)
                        @php $kelas = $enrollment->kelas; @endphp
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-start border-4" style="border-left-color: var(--uin-green) !important;">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $kelas->mataKuliah->nama_mk }}</h5>
                                    <p class="card-text small text-muted">
                                        <i class="bi bi-person me-1"></i>{{ $kelas->dosen->name }}<br>
                                        <i class="bi bi-door-open me-1"></i>Kelas {{ $kelas->nama_kelas }}<br>
                                        <i class="bi bi-calendar me-1"></i>{{ $kelas->tahun_ajaran }}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-uin btn-sm flex-grow-1">
                                            <i class="bi bi-eye me-1"></i>Lihat
                                        </a>
                                        <a href="{{ route('tugas.index', $kelas->id) }}" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-clipboard-check"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4" id="tugas">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-check me-2"></i>Submission Terbaru
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tugas</th>
                                <th>Mata Kuliah</th>
                                <th>Tanggal Submit</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSubmissions as $submission)
                                <tr>
                                    <td>{{ $submission->tugas->judul }}</td>
                                    <td>{{ $submission->tugas->kelas->mataKuliah->nama_mk }}</td>
                                    <td>{{ $submission->submitted_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($submission->status_revisi == 'pending') bg-warning
                                            @elseif($submission->status_revisi == 'diterima') bg-success
                                            @elseif($submission->status_revisi == 'revisi') bg-info
                                            @else bg-danger
                                            @endif">
                                            {{ ucfirst($submission->status_revisi) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($submission->nilai)
                                            <strong>{{ $submission->nilai }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('tugas.show', $submission->tugas_id) }}" class="btn btn-sm btn-uin">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada submission
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