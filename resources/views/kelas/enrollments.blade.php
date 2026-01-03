@extends('layouts.app')

@section('title', 'Kelola Mahasiswa')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kelas.show', $kelas->id) }}">{{ $kelas->mataKuliah->nama_mk }}</a></li>
            <li class="breadcrumb-item active">Kelola Mahasiswa</li>
        </ol>
    </nav>
    <h1><i class="bi bi-people-fill me-2"></i>Kelola Mahasiswa</h1>
    <p class="text-muted">{{ $kelas->mataKuliah->nama_mk }} - Kelas {{ $kelas->nama_kelas }}</p>
</div>

<div class="row">
    <!-- Add Mahasiswa Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Tambah Mahasiswa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('kelas.add-mahasiswa', $kelas->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="mahasiswa_id" class="form-label">Pilih Mahasiswa</label>
                        <select class="form-select" id="mahasiswa_id" name="mahasiswa_id" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($availableMahasiswa as $mhs)
                                <option value="{{ $mhs->id }}">
                                    {{ $mhs->name }} ({{ $mhs->nim_nip }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle me-1"></i>Tambah ke Kelas
                    </button>
                </form>

                @if($availableMahasiswa->isEmpty())
                    <div class="alert alert-info mt-3 mb-0">
                        <small>Semua mahasiswa sudah terdaftar di kelas ini.</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Kelas</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Kapasitas:</strong></p>
                <p class="mb-3">{{ $kelas->enrollments->count() }} / {{ $kelas->kapasitas }} mahasiswa</p>
                
                <p class="mb-2"><strong>Status:</strong></p>
                <p class="mb-0">
                    @if($kelas->enrollments->count() < $kelas->kapasitas)
                        <span class="badge bg-success">Masih Ada Tempat</span>
                    @else
                        <span class="badge bg-danger">Penuh</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Enrolled Mahasiswa List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Daftar Mahasiswa Terdaftar</h5>
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
                                <th>Aksi</th>
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
                                    <td>
                                        <form action="{{ route('kelas.remove-mahasiswa', [$kelas->id, $enrollment->mahasiswa_id]) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini dari kelas?')"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
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
@endsection