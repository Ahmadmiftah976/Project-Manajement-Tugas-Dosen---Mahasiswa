@extends('layouts.app')

@section('title', 'Tugas Saya')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-clipboard-check me-2"></i>Tugas Saya</h1>
    <p class="text-muted">Kelola semua tugas yang Anda buat</p>
</div>

<!-- Filter & Sort -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.tugas') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Urutkan</label>
                <select class="form-select" name="sort">
                    <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Terbaru</option>
                    <option value="deadline" {{ request('sort') == 'deadline' ? 'selected' : '' }}>Deadline</option>
                    <option value="judul" {{ request('sort') == 'judul' ? 'selected' : '' }}>Judul</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-uin w-100">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tugas List -->
<div class="row">
    @forelse($tugas as $t)
        @php
            $daysLeft = now()->diffInDays($t->deadline, false);
            $deadlineClass = $daysLeft <= 1 ? 'deadline-urgent' : ($daysLeft <= 3 ? 'deadline-soon' : 'deadline-normal');
        @endphp
        
        <div class="col-md-6 mb-4">
            <div class="card h-100 {{ $deadlineClass }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">{{ $t->judul }}</h5>
                        <span class="badge 
                            @if($t->status == 'published') bg-success
                            @elseif($t->status == 'draft') bg-warning text-dark
                            @else bg-secondary
                            @endif">
                            {{ ucfirst($t->status) }}
                        </span>
                    </div>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-book me-1"></i>{{ $t->kelas->mataKuliah->nama_mk }} (Kelas {{ $t->kelas->nama_kelas }})
                    </p>

                    <div class="mb-3">
                        <p class="mb-1 small">
                            <i class="bi bi-clock me-1"></i>
                            <strong>Deadline:</strong> {{ $t->deadline->format('d M Y, H:i') }}
                            @if($daysLeft >= 0)
                                <span class="badge bg-warning text-dark ms-2">{{ $daysLeft }} hari lagi</span>
                            @else
                                <span class="badge bg-danger ms-2">Terlambat</span>
                            @endif
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-tag me-1"></i>
                            <strong>Tipe:</strong> {{ ucfirst($t->tipe) }} | 
                            <strong>Bobot:</strong> {{ $t->bobot }}
                        </p>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary">
                                {{ $t->submissions()->count() }} Submission
                            </span>
                            <span class="badge bg-warning text-dark">
                                {{ $t->submissions()->where('status_revisi', 'pending')->count() }} Pending
                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-uin">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('tugas.edit', $t->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">Belum ada tugas</h4>
                    <p class="text-muted">Mulai dengan membuat tugas untuk kelas Anda</p>
                    <a href="{{ route('dosen.classes') }}" class="btn btn-uin mt-2">
                        <i class="bi bi-book me-1"></i>Lihat Kelas Saya
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($tugas->hasPages())
    <div class="mt-4">
        {{ $tugas->links() }}
    </div>
@endif
@endsection