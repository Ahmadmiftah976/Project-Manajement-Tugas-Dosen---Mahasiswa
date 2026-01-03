@extends('layouts.app')

@section('title', 'Penilaian Tugas - ' . $submission->mahasiswa->name)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold" style="color: #005f2f;"><i class="bi bi-journal-check me-2"></i>Penilaian Tugas</h2>
            <p class="text-muted mb-0">
                Memberikan nilai dan feedback untuk mahasiswa.
            </p>
        </div>
        <a href="{{ route('tugas.show', $submission->tugas_id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header text-white" style="background-color: #005f2f;">
                    <i class="bi bi-person-badge me-2"></i>Identitas Mahasiswa
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $submission->mahasiswa->name }}</h5>
                    <p class="text-muted mb-3">{{ $submission->mahasiswa->nim_nip ?? 'NIM Tidak Ada' }}</p>
                    
                    <hr>
                    
                    <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Judul Tugas</small>
                    <p class="fw-medium">{{ $submission->tugas->judul }}</p>
                    
                    <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Waktu Pengumpulan</small>
                    <p class="mb-0">
                        {{ $submission->submitted_at->format('d M Y, H:i') }}
                        @if($submission->is_late)
                            <span class="badge bg-danger ms-1">Terlambat</span>
                        @else
                            <span class="badge bg-success ms-1">Tepat Waktu</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <label class="fw-bold mb-2">File Tugas Mahasiswa</label>
                    <div class="d-grid">
                        <a href="{{ route('submissions.download', $submission->id) }}" class="btn btn-outline-primary py-2">
                            <i class="bi bi-file-earmark-arrow-down me-2"></i>Download File
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body bg-light">
                    <label class="fw-bold mb-2 small text-uppercase">Catatan Mahasiswa</label>
                    <p class="mb-0 fst-italic text-muted">"{{ $submission->catatan ?? 'Tidak ada catatan.' }}"</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold" style="color: #005f2f;">Form Penilaian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('submissions.updateStatus', $submission->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Penilaian</label>
                                <select class="form-select" name="status_revisi" required>
                                    <option value="diterima" {{ $submission->status_revisi == 'diterima' ? 'selected' : '' }}>Diterima (Selesai)</option>
                                    <option value="revisi" {{ $submission->status_revisi == 'revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                                    <option value="ditolak" {{ $submission->status_revisi == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nilai (0-100)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control fw-bold text-primary" name="nilai" min="0" max="100" value="{{ $submission->nilai }}" step="0.01">
                                    <span class="input-group-text">/ 100</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Feedback & Riwayat Diskusi</label>
                            
                            <div class="border rounded bg-light p-3 mb-3" style="height: 300px; overflow-y: auto;">
                                @forelse($submission->komentar as $kom)
                                    <div class="d-flex flex-column mb-3 {{ $kom->user->isDosen() ? 'align-items-end' : 'align-items-start' }}">
                                        <div class="d-flex align-items-center mb-1 {{ $kom->user->isDosen() ? 'flex-row-reverse' : '' }}">
                                            <strong class="small mx-2">{{ $kom->user->name }}</strong>
                                            <small class="text-muted" style="font-size: 0.7rem;">{{ $kom->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="p-3 rounded-3 shadow-sm {{ $kom->user->isDosen() ? 'bg-success text-white' : 'bg-white border text-dark' }}" style="max-width: 80%;">
                                            {{ $kom->komentar }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted opacity-50">
                                        <i class="bi bi-chat-square-dots display-4"></i>
                                        <p class="mt-2">Belum ada diskusi atau feedback.</p>
                                    </div>
                                @endforelse
                            </div>

                            <label class="form-label small text-muted">Tulis feedback atau instruksi revisi baru:</label>
                            <textarea class="form-control" name="komentar" rows="3" placeholder="Contoh: Bagian layout sudah bagus, tapi tolong perbaiki database..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tugas.show', $submission->tugas_id) }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-success px-4" style="background-color: #005f2f;">
                                <i class="bi bi-save me-2"></i>Simpan Penilaian & Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection