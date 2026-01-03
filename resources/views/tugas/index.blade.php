@extends('layouts.app')

@section('title', 'Daftar Tugas - ' . $kelas->mataKuliah->nama_mk)

@section('content')
<div class="container-fluid px-4">
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-body p-4 text-white" style="background-color: #005f2f;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-clipboard-check me-2"></i>Daftar Tugas</h2>
                    <p class="mb-0 opacity-75">
                        {{ $kelas->mataKuliah->nama_mk }} | Kelas {{ $kelas->nama_kelas }}
                    </p>
                </div>
                <a href="{{ route('mahasiswa.kelas.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">Judul Tugas</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Tipe & Bobot</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Deadline</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Status</th>
                                    <th class="pe-4 py-3 text-end text-secondary small fw-bold text-uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tugas as $t)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold d-block text-dark">{{ $t->judul }}</span>
                                            <small class="text-muted text-truncate" style="max-width: 250px; display:block;">
                                                {{ Str::limit(strip_tags($t->deskripsi), 50) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $t->tipe == 'individu' ? 'bg-info' : 'bg-primary' }} mb-1">
                                                {{ ucfirst($t->tipe) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">Bobot: {{ $t->bobot }}%</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ $t->deadline->format('d M Y, H:i') }}</span>
                                                @if($t->deadline->isFuture())
                                                    <small class="text-warning fw-bold">
                                                        {{ $t->deadline->diffForHumans() }}
                                                    </small>
                                                @else
                                                    <small class="text-danger fw-bold">Terlambat</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $submission = $t->submissions->where('mahasiswa_id', auth()->id())->first();
                                            @endphp

                                            @if($submission)
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                                </span>
                                            @elseif($t->deadline->isPast())
                                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Terlewat
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                    <i class="bi bi-dash-circle me-1"></i> Belum
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-success btn-sm" style="background-color: #005f2f; border:none;">
                                                <i class="bi bi-eye me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" style="width: 64px; opacity: 0.5;" class="mb-3">
                                            <p class="text-muted fw-bold">Belum ada tugas di mata kuliah ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($tugas->hasPages())
                        <div class="p-3 border-top">
                            {{ $tugas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection