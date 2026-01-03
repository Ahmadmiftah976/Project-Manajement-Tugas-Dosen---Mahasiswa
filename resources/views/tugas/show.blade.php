@extends('layouts.app')

@section('title', $tugas->judul)

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kelas.show', $tugas->kelas_id) }}">{{ $tugas->kelas->mataKuliah->nama_mk }}</a></li>
            <li class="breadcrumb-item active">{{ $tugas->judul }}</li>
        </ol>
    </nav>
    <h1><i class="bi bi-clipboard-check me-2"></i>{{ $tugas->judul }}</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Detail Tugas</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Mata Kuliah:</strong></p>
                        <p class="text-muted">{{ $tugas->kelas->mataKuliah->nama_mk }} (Kelas {{ $tugas->kelas->nama_kelas }})</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Dosen:</strong></p>
                        <p class="text-muted">{{ $tugas->kelas->dosen->name }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Deadline:</strong></p>
                        <p class="text-muted">
                            <i class="bi bi-clock me-1"></i>{{ $tugas->deadline->format('d M Y, H:i') }}
                            
                            {{-- PERBAIKAN DI SINI: Menggunakan diffForHumans --}}
                            @if($tugas->deadline->isFuture())
                                <span class="badge bg-warning text-dark ms-2">
                                    {{ $tugas->deadline->diffForHumans() }}
                                </span>
                            @else
                                <span class="badge bg-danger ms-2">
                                    Terlambat {{ $tugas->deadline->diffForHumans(null, true) }}
                                </span>
                            @endif
                            {{-- AKHIR PERBAIKAN --}}
                            
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Tipe:</strong></p>
                        <p class="text-muted">
                            <span class="badge bg-info">{{ ucfirst($tugas->tipe) }}</span>
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Bobot Nilai:</strong></p>
                        <p class="text-muted">{{ $tugas->bobot }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Pengumpulan Terlambat:</strong></p>
                        <p class="text-muted">
                            @if($tugas->allow_late_submission)
                                <span class="badge bg-success">Diizinkan</span>
                                @if($tugas->late_penalty > 0)
                                    <span class="badge bg-warning text-dark">Penalty: -{{ $tugas->late_penalty }}%</span>
                                @endif
                            @else
                                <span class="badge bg-danger">Tidak Diizinkan</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <h6><strong>Deskripsi:</strong></h6>
                    <p class="text-muted">{!! nl2br(e($tugas->deskripsi)) !!}</p>
                </div>

                @if($tugas->file_lampiran)
                    <div class="mb-3">
                        <h6><strong>File Lampiran:</strong></h6>
                        <a href="{{ Storage::url($tugas->file_lampiran) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download me-1"></i>Download Lampiran
                        </a>
                    </div>
                @endif

                @if(auth()->user()->isDosen() && $tugas->kelas->dosen_id == auth()->id())
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tugas.edit', $tugas->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i>Edit Tugas
                        </a>
                        <form action="{{ route('tugas.destroy', $tugas->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i>Hapus Tugas
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        @if(auth()->user()->isMahasiswa())
            @if($submission && $submission->status_revisi != 'revisi' && $submission->status_revisi != 'ditolak')
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Tugas Sudah Dikumpulkan</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Waktu Submit:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge 
                                @if($submission->status_revisi == 'pending') bg-warning
                                @elseif($submission->status_revisi == 'diterima') bg-success
                                @elseif($submission->status_revisi == 'revisi') bg-info
                                @else bg-danger
                                @endif">
                                {{ ucfirst($submission->status_revisi) }}
                            </span>
                        </p>
                        @if($submission->nilai)
                            <p><strong>Nilai:</strong> <span class="badge bg-primary fs-5">{{ $submission->nilai }}</span></p>
                        @endif
                        @if($submission->catatan)
                            <p><strong>Catatan:</strong></p>
                            <p class="text-muted">{{ $submission->catatan }}</p>
                        @endif
                        <a href="{{ route('submissions.download', $submission->id) }}" class="btn btn-primary">
                            <i class="bi bi-download me-1"></i>Download File Saya
                        </a>
                    </div>
                </div>
            @else
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-upload me-2"></i>
                            @if($submission && ($submission->status_revisi == 'revisi' || $submission->status_revisi == 'ditolak'))
                                Submit Ulang Tugas
                            @else
                                Kumpulkan Tugas
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($submission && $submission->status_revisi == 'revisi')
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Tugas perlu direvisi!</strong> Silakan perbaiki dan submit ulang.
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('submissions.store', $tugas->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="file_submission" class="form-label">File Tugas <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file_submission" name="file_submission" required>
                                <small class="text-muted">Format: PDF, DOC, DOCX, ZIP, RAR, PNG, JPG, JPEG (Max: 20MB)</small>
                            </div>

                            <button type="submit" class="btn btn-uin">
                                <i class="bi bi-send me-1"></i>Kumpulkan Tugas
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($submission)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Komentar & Feedback</h5>
                    </div>
                    <div class="card-body">
                        @forelse($submission->komentar as $komentar)
                            <div class="card mb-2 {{ $komentar->user->isDosen() ? 'border-success' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $komentar->user->name }}</strong>
                                        <small class="text-muted">{{ $komentar->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $komentar->komentar }}</p>
                                    @if($komentar->file_lampiran)
                                        <a href="{{ route('komentar.download', $komentar->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Lampiran
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum ada komentar</p>
                        @endforelse

                        <form action="{{ route('komentar.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <input type="hidden" name="submission_id" value="{{ $submission->id }}">
                            <div class="mb-2">
                                <textarea class="form-control" name="komentar" rows="2" placeholder="Tulis komentar..." required></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="file" class="form-control form-control-sm" name="file_lampiran">
                                <button type="submit" class="btn btn-sm btn-uin">Kirim</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        @if(auth()->user()->isDosen() && $submissions)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Daftar Submission</h5>
                    <span class="badge bg-primary">{{ $submissions->count() }} Mahasiswa</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Waktu Submit</th>
                                    <th>Status</th>
                                    <th>Nilai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submissions as $sub)
                                    <tr>
                                        <td>
                                            <strong>{{ $sub->mahasiswa->name }}</strong><br>
                                            <small class="text-muted">{{ $sub->mahasiswa->nim_nip }}</small>
                                        </td>
                                        <td>
                                            {{ $sub->submitted_at->format('d M Y, H:i') }}
                                            @if($sub->is_late)
                                                <br><span class="badge bg-danger">Terlambat</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($sub->status_revisi == 'pending') bg-warning
                                                @elseif($sub->status_revisi == 'diterima') bg-success
                                                @elseif($sub->status_revisi == 'revisi') bg-info
                                                @else bg-danger
                                                @endif">
                                                {{ ucfirst($sub->status_revisi) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($sub->nilai)
                                                <strong>{{ $sub->nilai }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('submissions.grade', $sub->id) }}" class="btn btn-sm btn-success" style="background-color: #005f2f; border:none;">
                                                <i class="bi bi-pencil-square me-1"></i> Periksa / Nilai
                                            </a>>
                                            <a href="{{ route('submissions.download', $sub->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="gradeModal{{ $sub->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Nilai Submission - {{ $sub->mahasiswa->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('submissions.updateStatus', $sub->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3 p-3 bg-light rounded border">
                                                            <small class="text-muted fw-bold d-block mb-1">Catatan Mahasiswa:</small>
                                                            <p class="mb-0 text-dark small fst-italic">
                                                                "{{ $sub->catatan ?? 'Tidak ada catatan' }}"
                                                            </p>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-uppercase text-muted">Riwayat Diskusi</label>
                                                            <div class="border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto;">
                                                                @forelse($sub->komentar as $kom)
                                                                    <div class="d-flex flex-column mb-2 {{ $kom->user->isDosen() ? 'align-items-end' : 'align-items-start' }}">
                                                                        <div class="p-2 rounded {{ $kom->user->isDosen() ? 'bg-success text-white' : 'bg-light border text-dark' }}" style="max-width: 85%;">
                                                                            <small class="d-block fw-bold mb-1 {{ $kom->user->isDosen() ? 'text-light' : 'text-primary' }}" style="font-size: 0.7rem;">
                                                                                {{ $kom->user->name }} • {{ $kom->created_at->format('d/m H:i') }}
                                                                            </small>
                                                                            <p class="mb-0 small">{{ $kom->komentar }}</p>
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                    <div class="text-center py-4">
                                                                        <img src="https://cdn-icons-png.flaticon.com/512/1380/1380338.png" style="width: 40px; opacity: 0.3" alt="No Chat">
                                                                        <p class="text-muted small mt-2">Belum ada riwayat diskusi.</p>
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Status</label>
                                                                <select class="form-select" name="status_revisi" required>
                                                                    <option value="diterima" {{ $sub->status_revisi == 'diterima' ? 'selected' : '' }}>Diterima (Selesai)</option>
                                                                    <option value="revisi" {{ $sub->status_revisi == 'revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                                                                    <option value="ditolak" {{ $sub->status_revisi == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Nilai (0-100)</label>
                                                                <input type="number" class="form-control" name="nilai" min="0" max="100" value="{{ $sub->nilai }}" step="0.01">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Balasan / Komentar Baru</label>
                                                            <textarea class="form-control" name="komentar" rows="3" placeholder="Tulis instruksi revisi atau feedback disini..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-uin">Simpan Nilai</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada submission
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Status Tugas:</strong></p>
                <p class="mb-3">
                    <span class="badge 
                        @if($tugas->status == 'published') bg-success
                        @elseif($tugas->status == 'draft') bg-warning
                        @else bg-secondary
                        @endif">
                        {{ ucfirst($tugas->status) }}
                    </span>
                </p>

                <p class="mb-2"><strong>Dibuat:</strong></p>
                <p class="text-muted mb-3">{{ $tugas->created_at->format('d M Y, H:i') }}</p>

                @if(auth()->user()->isMahasiswa())
                    <hr>
                    <p class="mb-2"><strong>Status Pengumpulan Anda:</strong></p>
                    @if($submission)
                        <span class="badge bg-success">Sudah Dikumpulkan</span>
                    @else
                        <span class="badge bg-warning">Belum Dikumpulkan</span>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection