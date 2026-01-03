@extends('layouts.app')

@section('title', 'Mata Kuliah Saya')

@section('content')
<div class="page-header mb-4">
    <h1 class="fw-bold" style="color: #005f2f;"><i class="bi bi-book-half me-2"></i>Mata Kuliah Saya</h1>
    <p class="text-muted">Daftar mata kuliah yang Anda ambil semester ini.</p>
</div>

<div class="row">
    @forelse($enrollments as $enrollment)
        @php $kelas = $enrollment->kelas; @endphp
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title fw-bold mb-1 text-dark">{{ $kelas->mataKuliah->nama_mk }}</h5>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-bookmark me-1"></i> {{ $kelas->mataKuliah->kode_mk ?? 'KODE-MK' }} 
                                <span class="mx-1">|</span> 
                                <i class="bi bi-door-open me-1"></i> Kelas {{ $kelas->nama_kelas }}
                            </p>
                        </div>
                        <span class="badge bg-success rounded-pill px-3">Aktif</span>
                    </div>

                    <hr class="my-3 text-muted" style="opacity: 0.1;">

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <small class="text-muted d-block mb-1">Tahun Ajaran</small>
                            <span class="fw-medium text-dark">{{ $kelas->tahun_ajaran }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1">Semester</small>
                            <span class="fw-medium text-dark">{{ ucfirst($kelas->semester) }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1">Dosen Pengampu</small>
                            <span class="fw-medium text-dark text-truncate d-block">{{ $kelas->dosen->name }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1">Ruangan</small>
                            <span class="fw-medium text-dark">{{ $kelas->ruangan ?? 'Daring' }}</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-success flex-grow-1" style="background-color: #005f2f; border-color: #005f2f;">
                            <i class="bi bi-eye me-2"></i>Lihat Detail
                        </a>
                        <a href="{{ route('tugas.index', $kelas->id) }}" class="btn btn-warning text-white" title="Lihat Tugas">
                            <i class="bi bi-clipboard-check"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-light border shadow-sm text-center py-5 rounded-3">
                <i class="bi bi-info-circle display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">Belum ada mata kuliah diambil.</h5>
                <p class="mb-0 text-muted">Pastikan KRS Anda sudah disetujui.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection