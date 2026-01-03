@extends('layouts.app')

@section('title', $kelas->mataKuliah->nama_mk)

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $kelas->mataKuliah->nama_mk }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-book-fill me-2"></i>{{ $kelas->mataKuliah->nama_mk }}</h1>
        @if(auth()->user()->isDosen() && $kelas->dosen_id == auth()->id())
            <a href="{{ route('tugas.create', $kelas->id) }}" class="btn btn-uin">
                <i class="bi bi-plus-circle me-1"></i>Buat Tugas Baru
            </a>
        @endif
    </div>
</div>

<!-- Class Info -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Kelas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Kode Mata Kuliah:</strong></p>
                        <p class="mb-3">{{ $kelas->mataKuliah->kode_mk }}</p>

                        <p class="mb-2"><strong>SKS:</strong></p>
                        <p class="mb-3">{{ $kelas->mataKuliah->sks }} SKS</p>

                        <p class="mb-2"><strong>Dosen:</strong></p>
                        <p class="mb-3">{{ $kelas->dosen->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Kelas:</strong></p>
                        <p class="mb-3">{{ $kelas->nama_kelas }}</p>

                        <p class="mb-2"><strong>Tahun Ajaran:</strong></p>
                        <p class="mb-3">{{ $kelas->tahun_ajaran }} ({{ ucfirst($kelas->semester) }})</p>

                        <p class="mb-2"><strong>Ruangan:</strong></p>
                        <p class="mb-3">{{ $kelas->ruangan ?? '-' }}</p>
                    </div>
                </div>

                @if($kelas->mataKuliah->deskripsi)
                    <hr>
                    <p class="mb-2"><strong>Deskripsi:</strong></p>
                    <p class="text-muted mb-0">{{ $kelas->mataKuliah->deskripsi }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistik</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Mahasiswa:</span>
                    <strong>{{ $stats['total_mahasiswa'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Tugas:</span>
                    <strong>{{ $stats['total_tugas'] }}</strong>
                </div>
                @if(auth()->user()->isDosen())
                    <div class="d-flex justify-content-between">
                        <span>Perlu Dinilai:</span>
                        <strong class="text-warning">{{ $stats['pending_submissions'] ?? 0 }}</strong>
                    </div>
                @endif
            </div>
        </div>

        @if(auth()->user()->isDosen() && $kelas->dosen_id == auth()->id())
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Kelola</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('kelas.enrollments', $kelas->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="bi bi-people me-1"></i>Kelola Mahasiswa
                    </a>
                    <a href="{{ route('kelas.edit', $kelas->id) }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                        <i class="bi bi-pencil me-1"></i>Edit Kelas
                    </a>
                    <a href="{{ route('dosen.mahasiswa.list', $kelas->id) }}" class="btn btn-outline-info btn-sm w-100">
                        <i class="bi bi-list-check me-1"></i>Daftar Nilai
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Tugas List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Daftar Tugas</h5>
                <span class="badge bg-primary">{{ $tugas->total() }} Tugas</span>
            </div>
            <div class="card-body">
                @forelse($tugas as $t)
                    @php
                        $now = now();
                        $deadline = $t->deadline;
                        if ($now->lt($deadline)) {
                            $diff = $now->diff($deadline);
                            $daysLeft = $diff->days;
                            $hoursLeft = $diff->h;
                        } else {
                            $daysLeft = -1;
                            $hoursLeft = 0;
                        }
                        $deadlineClass = $daysLeft <= 1 ? 'deadline-urgent' : ($daysLeft <= 3 ? 'deadline-soon' : 'deadline-normal');
                        
                        $submission = null;
                        if(auth()->user()->isMahasiswa()) {
                            $submission = $t->submissions()->where('mahasiswa_id', auth()->id())->latest('attempt')->first();
                        }
                    @endphp
                    
                    <div class="card mb-3 {{ $deadlineClass }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">{{ $t->judul }}</h6>
                                    <p class="text-muted mb-2 small">
                                        <i class="bi bi-clock me-1"></i>Deadline: {{ $t->deadline->format('d M Y, H:i') }}
                                        @if($daysLeft >= 0)
                                            <span class="badge bg-warning text-dark ms-2">
                                                @if($daysLeft > 0)
                                                    {{ $daysLeft }} hari lagi
                                                @elseif($hoursLeft > 0)
                                                    {{ $hoursLeft }} jam lagi
                                                @else
                                                    Kurang dari 1 jam lagi
                                                @endif
                                            </span>
                                        @else
                                            <span class="badge bg-danger ms-2">Terlambat</span>
                                        @endif
                                    </p>
                                    <div>
                                        <span class="badge bg-info">{{ ucfirst($t->tipe) }}</span>
                                        <span class="badge bg-secondary">Bobot: {{ $t->bobot }}</span>
                                        @if(auth()->user()->isMahasiswa() && $submission)
                                            <span class="badge 
                                                @if($submission->status_revisi == 'pending') bg-warning
                                                @elseif($submission->status_revisi == 'diterima') bg-success
                                                @elseif($submission->status_revisi == 'revisi') bg-info
                                                @else bg-danger
                                                @endif">
                                                {{ ucfirst($submission->status_revisi) }}
                                            </span>
                                            @if($submission->nilai)
                                                <span class="badge bg-primary">Nilai: {{ $submission->nilai }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-uin">
                                        <i class="bi bi-eye me-1"></i>Lihat Detail
                                    </a>
                                    @if(auth()->user()->isDosen() && $kelas->dosen_id == auth()->id())
                                        <a href="{{ route('tugas.edit', $t->id) }}" class="btn btn-warning btn-sm mt-2">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-1"></i>
                        <p class="mt-3">Belum ada tugas untuk kelas ini</p>
                        @if(auth()->user()->isDosen() && $kelas->dosen_id == auth()->id())
                            <a href="{{ route('tugas.create', $kelas->id) }}" class="btn btn-uin mt-2">
                                <i class="bi bi-plus-circle me-1"></i>Buat Tugas Pertama
                            </a>
                        @endif
                    </div>
                @endforelse

                @if($tugas->hasPages())
                    <div class="mt-3">
                        {{ $tugas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Mahasiswa List (for Dosen) -->
@if(auth()->user()->isDosen() && $kelas->dosen_id == auth()->id())
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Daftar Mahasiswa</h5>
                    <a href="{{ route('kelas.enrollments', $kelas->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-gear me-1"></i>Kelola
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kelas->enrollments as $enrollment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $enrollment->mahasiswa->nim_nip }}</td>
                                        <td>{{ $enrollment->mahasiswa->name }}</td>
                                        <td>{{ $enrollment->mahasiswa->email }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($enrollment->status == 'active') bg-success
                                                @elseif($enrollment->status == 'dropped') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada mahasiswa terdaftar
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
@endif
@endsection