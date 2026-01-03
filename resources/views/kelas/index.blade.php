@extends('layouts.app')

@section('title', 'Kelas Saya')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-book-fill me-2"></i>Kelas Saya</h1>
        <a href="{{ route('kelas.create') }}" class="btn btn-uin">
            <i class="bi bi-plus-circle me-1"></i>Buat Kelas Baru
        </a>
    </div>
</div>

<div class="row">
    @forelse($kelas as $k)
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-start border-4" style="border-left-color: var(--uin-green) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-1">{{ $k->mataKuliah->nama_mk }}</h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-bookmark me-1"></i>{{ $k->mataKuliah->kode_mk }} | 
                                <i class="bi bi-door-open me-1"></i>Kelas {{ $k->nama_kelas }}
                            </p>
                        </div>
                        <span class="badge {{ $k->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $k->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Tahun Ajaran</small>
                            <p class="mb-0">{{ $k->tahun_ajaran }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Semester</small>
                            <p class="mb-0">{{ ucfirst($k->semester) }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Ruangan</small>
                            <p class="mb-0">{{ $k->ruangan ?? '-' }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Mahasiswa</small>
                            <p class="mb-0">
                                <i class="bi bi-people me-1"></i>{{ $k->enrollments->count() }} / {{ $k->kapasitas }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('kelas.show', $k->id) }}" class="btn btn-uin btn-sm flex-grow-1">
                            <i class="bi bi-eye me-1"></i>Lihat Detail
                        </a>
                        <a href="{{ route('tugas.create', $k->id) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-plus"></i> Tugas
                        </a>
                        <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">Belum ada kelas</h4>
                    <p class="text-muted">Mulai dengan membuat kelas pertama Anda</p>
                    <a href="{{ route('kelas.create') }}" class="btn btn-uin mt-2">
                        <i class="bi bi-plus-circle me-1"></i>Buat Kelas Pertama
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($kelas->hasPages())
    <div class="mt-4">
        {{ $kelas->links() }}
    </div>
@endif
@endsection