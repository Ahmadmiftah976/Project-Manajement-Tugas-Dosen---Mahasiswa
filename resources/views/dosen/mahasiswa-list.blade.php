@extends('layouts.app')

@section('title', 'Daftar Nilai Mahasiswa')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kelas.show', $kelas->id) }}">{{ $kelas->mataKuliah->nama_mk }}</a></li>
            <li class="breadcrumb-item active">Daftar Nilai</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-list-check me-2"></i>Daftar Nilai Mahasiswa</h1>
            <p class="text-muted">{{ $kelas->mataKuliah->nama_mk }} - Kelas {{ $kelas->nama_kelas }}</p>
        </div>
        <a href="{{ route('dosen.export.report', $kelas->id) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Total Tugas</th>
                        <th>Submitted</th>
                        <th>Diterima</th>
                        <th>Rata-rata Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas->enrollments as $enrollment)
                        @php
                            $mahasiswa = $enrollment->mahasiswa;
                            $stats = $mahasiswaStats[$mahasiswa->id] ?? [
                                'total_tugas' => 0,
                                'submitted' => 0,
                                'diterima' => 0,
                                'rata_nilai' => 0
                            ];
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mahasiswa->nim_nip }}</td>
                            <td>{{ $mahasiswa->name }}</td>
                            <td>{{ $stats['total_tugas'] }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $stats['submitted'] }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $stats['diterima'] }}</span>
                            </td>
                            <td>
                                @if($stats['rata_nilai'] > 0)
                                    <strong class="text-primary">{{ number_format($stats['rata_nilai'], 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('dosen.mahasiswa.detail', [$kelas->id, $mahasiswa->id]) }}" 
                                   class="btn btn-sm btn-uin">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada mahasiswa terdaftar
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
@if($kelas->enrollments->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-primary">{{ $kelas->enrollments->count() }}</h3>
                <p class="mb-0 text-muted">Total Mahasiswa</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $totalSubmitted = collect($mahasiswaStats)->sum('submitted');
                @endphp
                <h3 class="text-success">{{ $totalSubmitted }}</h3>
                <p class="mb-0 text-muted">Total Submission</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $totalDiterima = collect($mahasiswaStats)->sum('diterima');
                @endphp
                <h3 class="text-info">{{ $totalDiterima }}</h3>
                <p class="mb-0 text-muted">Tugas Diterima</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $avgClass = collect($mahasiswaStats)->where('rata_nilai', '>', 0)->avg('rata_nilai');
                @endphp
                <h3 class="text-warning">{{ $avgClass ? number_format($avgClass, 2) : '-' }}</h3>
                <p class="mb-0 text-muted">Rata-rata Kelas</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection