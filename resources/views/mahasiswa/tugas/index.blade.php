@extends('layouts.app')

@section('title', 'Tugas Saya')

@section('content')
<div class="page-header mb-4">
    <h1 class="fw-bold" style="color: #005f2f;"><i class="bi bi-list-check me-2"></i>Tugas Saya</h1>
    <p class="text-muted">Kelola dan kerjakan tugas dari seluruh mata kuliah.</p>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white h-100" style="background-color: #005f2f;">
            <div class="card-body p-3">
                <h1 class="fw-bold mb-0">{{ $stats['total'] }}</h1>
                <p class="mb-0 small opacity-75"><i class="bi bi-layers me-1"></i>Total Tugas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white h-100" style="background-color: #005f2f;">
            <div class="card-body p-3">
                <h1 class="fw-bold mb-0">{{ $stats['pending'] }}</h1>
                <p class="mb-0 small opacity-75"><i class="bi bi-clock-history me-1"></i>Perlu Dikerjakan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white h-100" style="background-color: #005f2f;">
            <div class="card-body p-3">
                <h1 class="fw-bold mb-0">{{ $stats['selesai'] }}</h1>
                <p class="mb-0 small opacity-75"><i class="bi bi-check-circle me-1"></i>Sudah Dikumpulkan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white h-100" style="background-color: #005f2f;">
            <div class="card-body p-3">
                <h1 class="fw-bold mb-0">{{ $stats['terlambat'] }}</h1>
                <p class="mb-0 small opacity-75"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-3 border-bottom d-flex gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-2">Semua Tugas</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Judul Tugas</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Mata Kuliah</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Waktu & Deadline</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugas as $t)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold d-block text-dark">{{ $t->judul }}</span>
                                <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ ucfirst($t->tipe) }}</span>
                            </td>
                            <td>
                                <span class="d-block fw-medium">{{ $t->kelas->mataKuliah->nama_mk }}</span>
                                <small class="text-muted">Kelas {{ $t->kelas->nama_kelas }}</small>
                            </td>
                            <td>
                                <small class="d-block text-muted">{{ $t->deadline->format('d M Y, H:i') }}</small>
                                @if($t->deadline->isFuture())
                                    <small class="fw-bold text-warning">{{ $t->deadline->diffForHumans() }}</small>
                                @else
                                    <small class="fw-bold text-danger">Terlambat</small>
                                @endif
                            </td>
                            <td>
                                @php $submission = $t->submissions->first(); @endphp

                                @if($submission)
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                    </span>
                                @elseif($t->deadline->isPast())
                                    <span class="badge bg-danger rounded-pill px-3 py-2">
                                        <i class="bi bi-x-circle-fill me-1"></i> Terlewat
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        <i class="bi bi-hourglass-split me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-sm btn-success" style="background-color: #005f2f; border:none;" title="Kerjakan / Detail">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" style="width: 60px; opacity: 0.5;" class="mb-3 d-block mx-auto">
                                Belum ada tugas yang tersedia.
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
@endsection