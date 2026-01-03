<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pengelolaan Tugas UIN Alauddin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --uin-green: #006838;
            --uin-green-dark: #004d28;
        }
        
        body {
            background: linear-gradient(135deg, var(--uin-green) 0%, var(--uin-green-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-left {
            background: linear-gradient(135deg, var(--uin-green) 0%, var(--uin-green-dark) 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-left h2 {
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .login-right {
            padding: 50px;
        }
        
        .form-control:focus {
            border-color: var(--uin-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 104, 56, 0.25);
        }
        
        .btn-login {
            background-color: var(--uin-green);
            color: white;
            padding: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background-color: var(--uin-green-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,104,56,0.3);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section i {
            font-size: 4rem;
            color: var(--uin-green);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card row g-0">
            <div class="col-md-5 login-left">
                <div>
                    <h2><i class="bi bi-mortarboard-fill"></i></h2>
                    <h2>Sistem Pengelolaan Tugas</h2>
                    <p class="lead">UIN Alauddin Makassar</p>
                    <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
                    <p>Platform manajemen tugas dan konsultasi akademik untuk mahasiswa dan dosen</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Kelola tugas dengan mudah</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Tracking status revisi</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Notifikasi deadline otomatis</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-7 login-right">
                <div class="logo-section">
                    <i class="bi bi-person-circle"></i>
                    <h3 class="mt-3 mb-4">Masuk ke Akun Anda</h3>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Kampus</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="nama@uin-alauddin.ac.id" 
                                   value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-login w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>

                    <div class="text-center">
                        <p class="mb-0">Belum punya akun? <a href="{{ route('register') }}" style="color: var(--uin-green);">Daftar sekarang</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>