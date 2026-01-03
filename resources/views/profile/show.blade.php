@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-person-circle me-2"></i>Profil Saya</h1>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded-circle" width="150" height="150">
                    @else
                        <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                            <span class="text-white display-3">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->nim_nip }}</p>
                <span class="badge badge-uin text-uppercase">{{ $user->role }}</span>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistik</h6>
            </div>
            <div class="card-body">
                @if($user->isMahasiswa())
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Kelas:</span>
                        <strong>{{ $stats['total_kelas'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Tugas:</span>
                        <strong>{{ $stats['total_tugas'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Tugas Selesai:</span>
                        <strong class="text-success">{{ $stats['tugas_selesai'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Rata-rata Nilai:</span>
                        <strong class="text-primary">{{ number_format($stats['rata_rata_nilai'], 2) }}</strong>
                    </div>
                @elseif($user->isDosen())
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Kelas:</span>
                        <strong>{{ $stats['total_kelas'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Mahasiswa:</span>
                        <strong>{{ $stats['total_mahasiswa'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Tugas:</span>
                        <strong>{{ $stats['total_tugas'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Perlu Dinilai:</span>
                        <strong class="text-warning">{{ $stats['pending_grading'] }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="col-md-8">
        <!-- Edit Profile -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Profil</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nim_nip" class="form-label">NIM/NIP</label>
                            <input type="text" class="form-control" value="{{ $user->nim_nip }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="prodi" class="form-label">Program Studi</label>
                            <input type="text" class="form-control" value="{{ $user->prodi }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="fakultas" class="form-label">Fakultas</label>
                            <input type="text" class="form-control" value="{{ $user->fakultas }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $user->phone }}">
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">Foto Profil</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        <small class="text-muted">Format: JPG, JPEG, PNG (Max: 2MB)</small>
                        @if($user->avatar)
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteAvatar()">
                                    <i class="bi bi-trash me-1"></i>Hapus Foto
                                </button>
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-uin">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimal 8 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key me-1"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteAvatar() {
    if(confirm('Yakin ingin menghapus foto profil?')) {
        fetch('{{ route("profile.deleteAvatar") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(response => {
            if(response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection