@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-bell-fill me-2"></i>Notifikasi</h1>
        @if($unreadCount > 0)
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-success">
                    <i class="bi bi-check-all me-1"></i>Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('notifications.index') }}">
            Semua <span class="badge bg-secondary ms-1">{{ $notifications->total() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('notifications.index', ['filter' => 'unread']) }}">
            Belum Dibaca <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
        </a>
    </li>
</ul>

<!-- Notifications List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @forelse($notifications as $notification)
                    <div class="card mb-2 {{ !$notification->is_read ? 'border-primary' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <!-- Icon based on type -->
                                        @if($notification->type == 'deadline')
                                            <i class="bi bi-calendar-x text-danger fs-4 me-2"></i>
                                        @elseif($notification->type == 'submission')
                                            <i class="bi bi-file-earmark-check text-info fs-4 me-2"></i>
                                        @elseif($notification->type == 'comment')
                                            <i class="bi bi-chat-left-text text-warning fs-4 me-2"></i>
                                        @elseif($notification->type == 'grade')
                                            <i class="bi bi-star text-success fs-4 me-2"></i>
                                        @else
                                            <i class="bi bi-bell text-primary fs-4 me-2"></i>
                                        @endif
                                        
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $notification->title }}
                                                @if(!$notification->is_read)
                                                    <span class="badge bg-primary">Baru</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    
                                    <p class="mb-2">{{ $notification->message }}</p>
                                    
                                    @if($notification->link)
                                        <a href="{{ $notification->link }}" class="btn btn-sm btn-uin" onclick="markAsRead({{ $notification->id }})">
                                            <i class="bi bi-eye me-1"></i>Lihat Detail
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(!$notification->is_read)
                                            <li>
                                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check me-2"></i>Tandai Dibaca
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bell-slash display-1"></i>
                        <p class="mt-3">Tidak ada notifikasi</p>
                    </div>
                @endforelse

                @if($notifications->hasPages())
                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    });
}
</script>
@endpush
@endsection