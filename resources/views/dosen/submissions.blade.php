@extends('layouts.app')

@section('title', 'Submission Masuk')

@section('content')
<div class="page-header mb-4">
    <h1 class="fw-bold" style="color: #005f2f;"><i class="bi bi-inbox-fill me-2"></i>Submission Masuk</h1>
    <p class="text-muted">Kelola dan nilai submission dari mahasiswa di semua kelas.</p>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card text-white h-100 border-0 shadow-sm" style="background-color: #005f2f;">
            <div class="card-body">
                <h2 class="fw-bold mb-0">{{ $submissions->count() }}</h2>
                <p class="mb-0 opacity-75"><i class="bi bi-folder2-open me-2"></i>Total Submission</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white h-100 border-0 shadow-sm" style="background-color: #005f2f;">
            <div class="card-body">
                <h2 class="fw-bold mb-0">{{ $submissions->where('status_revisi', 'pending')->count() }}</h2>
                <p class="mb-0 opacity-75"><i class="bi bi-clock-history me-2"></i>Perlu Dinilai</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white h-100 border-0 shadow-sm" style="background-color: #005f2f;">
            <div class="card-body">
                <h2 class="fw-bold mb-0">{{ $submissions->where('status_revisi', 'diterima')->count() }}</h2>
                <p class="mb-0 opacity-75"><i class="bi bi-check-circle me-2"></i>Diterima</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white h-100 border-0 shadow-sm" style="background-color: #005f2f;">
            <div class="card-body">
                <h2 class="fw-bold mb-0">{{ $submissions->where('status_revisi', 'revisi')->count() }}</h2>
                <p class="mb-0 opacity-75"><i class="bi bi-arrow-repeat me-2"></i>Revisi</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-3 border-bottom d-flex gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-2">Semua Submission</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 small fw-bold text-uppercase text-secondary">Mahasiswa</th>
                        <th class="py-3 small fw-bold text-uppercase text-secondary">Tugas</th>
                        <th class="py-3 small fw-bold text-uppercase text-secondary">Mata Kuliah</th>
                        <th class="py-3 small fw-bold text-uppercase text-secondary">Waktu Submit</th>
                        <th class="py-3 small fw-bold text-uppercase text-secondary">Status</th>
                        <th class="py-3 small fw-bold text-uppercase text-secondary">Nilai</th>
                        <th class="pe-4 py-3 text-end small fw-bold text-uppercase text-secondary">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $sub)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold d-block text-dark">{{ $sub->mahasiswa->name }}</span>
                                <small class="text-muted">{{ $sub->mahasiswa->nim_nip }}</small>
                            </td>
                            <td>
                                <span class="fw-medium text-dark">{{ $sub->tugas->judul }}</span>
                            </td>
                            <td>
                                <span class="d-block text-dark">{{ $sub->tugas->kelas->mataKuliah->nama_mk }}</span>
                                <small class="text-muted">Kelas {{ $sub->tugas->kelas->nama_kelas }}</small>
                            </td>
                            <td>
                                {{ $sub->submitted_at->format('d M Y, H:i') }}
                                @if($sub->is_late)
                                    <br><span class="badge bg-danger" style="font-size: 0.65rem;">Terlambat</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 
                                    @if($sub->status_revisi == 'pending') bg-warning text-dark
                                    @elseif($sub->status_revisi == 'diterima') bg-success
                                    @elseif($sub->status_revisi == 'revisi') bg-info
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($sub->status_revisi) }}
                                </span>
                            </td>
                            <td>
                                @if($sub->nilai)
                                    <span class="fw-bold text-primary fs-6">{{ $sub->nilai }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('submissions.grade', $sub->id) }}" class="btn btn-sm btn-success text-white" style="background-color: #005f2f; border:none;" title="Periksa / Nilai">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    
                                    <a href="{{ route('submissions.download', $sub->id) }}" class="btn btn-sm btn-outline-primary" title="Download File">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                Belum ada submission masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($submissions, 'links'))
            <div class="p-3 border-top">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection