<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Pengelolaan Tugas') - UIN Alauddin Makassar</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --uin-green: #006838;
            --uin-green-dark: #004d28;
            --uin-green-light: #e8f5e9;
            --uin-white: #ffffff;
            --uin-gray: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--uin-gray);
        }
        
        .navbar-uin {
            background: linear-gradient(135deg, var(--uin-green) 0%, var(--uin-green-dark) 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-uin .navbar-brand {
            color: var(--uin-white) !important;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .navbar-uin .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .navbar-uin .nav-link:hover {
            color: var(--uin-white) !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        
        .navbar-uin .nav-link.active {
            color: var(--uin-white) !important;
            background-color: rgba(255,255,255,0.15);
            border-radius: 5px;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: var(--uin-white);
            box-shadow: 2px 0 5px rgba(0,0,0,.05);
            padding: 20px 0;
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--uin-green-light);
            color: var(--uin-green);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--uin-green);
            color: var(--uin-white);
        }
        
        .sidebar .nav-link i {
            width: 25px;
        }
        
        .btn-uin {
            background-color: var(--uin-green);
            color: var(--uin-white);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-uin:hover {
            background-color: var(--uin-green-dark);
            color: var(--uin-white);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,104,56,0.3);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }
        
        .card-header {
            background-color: var(--uin-white);
            border-bottom: 2px solid var(--uin-green);
            font-weight: 600;
            color: var(--uin-green);
            padding: 15px 20px;
        }
        
        .badge-uin {
            background-color: var(--uin-green);
            color: var(--uin-white);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--uin-green) 0%, var(--uin-green-dark) 100%);
            color: var(--uin-white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .deadline-urgent {
            border-left: 4px solid #dc3545;
        }
        
        .deadline-soon {
            border-left: 4px solid #ffc107;
        }
        
        .deadline-normal {
            border-left: 4px solid var(--uin-green);
        }
        
        .status-pending {
            color: #ffc107;
        }
        
        .status-diterima {
            color: #28a745;
        }
        
        .status-revisi {
            color: #fd7e14;
        }
        
        .status-ditolak {
            color: #dc3545;
        }
        
        .main-content {
            padding: 25px;
        }
        
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--uin-green-light);
        }
        
        .page-header h1 {
            color: var(--uin-green);
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item.active {
            color: var(--uin-green);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-uin navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-mortarboard-fill me-2"></i>
                SPTM - UIN Alauddin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell me-1"></i> Notifikasi
                            @if(auth()->user()->unreadNotifications()->count() > 0)
                                <span class="notification-badge">{{ auth()->user()->unreadNotifications()->count() }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        @if(auth()->user()->isMahasiswa())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="bi bi-grid-fill"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mahasiswa.kelas.index') }}" class="nav-link {{ request()->routeIs('mahasiswa.kelas.*') ? 'active' : '' }}">
                                    <i class="bi bi-book me-2"></i> Mata Kuliah Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mahasiswa.tugas.index') }}" class="nav-link {{ request()->routeIs('mahasiswa.tugas.*') ? 'active' : '' }}">
                                    <i class="bi bi-list-task me-2"></i> Tugas Saya
                                </a>
                            </li>
                        @elseif(auth()->user()->isDosen())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="bi bi-grid-fill"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.classes') || request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('dosen.classes') }}">
                                    <i class="bi bi-book-fill"></i> Kelas Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.submissions') ? 'active' : '' }}" href="{{ route('dosen.submissions') }}">
                                    <i class="bi bi-inbox-fill"></i> Submission Masuk
                                </a>
                            </li>
                            
                        @endif
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>