<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Pengelolaan Tugas UIN Alauddin</title>
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
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section i {
            font-size: 4rem;
            color: var(--uin-green);
        }
        
        .form-control:focus {
            border-color: var(--uin-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 104, 56, 0.25);
        }
        
        .btn-register {
            background-color: var(--uin-green);
            color: white;
            padding: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background-color: var(--uin-green-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,104,56,0.3);
        }

        .info-box {
            background-color: #e8f5e9;
            border-left: 4px solid var(--uin-green);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo-section">
            <i class="bi bi-person-plus-fill"></i>
            <h3 class="mt-3 mb-2">Daftar Akun Baru</h3>
            <p class="text-muted">Sistem Pengelolaan Tugas - UIN Alauddin Makassar</p>
        </div>

        <div class="info-box">
            <small>
                <i class="bi bi-info-circle me-2"></i>
                <strong>Penting:</strong> Gunakan email kampus (@uin-alauddin.ac.id) dan NIM/NIP yang terdaftar di database kampus.
            </small>
        </div>

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

        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="mb-3">
                <label for="nim_nip" class="form-label">NIM/NIP <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                    <input type="text" class="form-control" id="nim_nip" name="nim_nip" 
                           placeholder="Masukkan NIM atau NIP" 
                           value="{{ old('nim_nip') }}" required>
                </div>
                <small class="text-muted">Gunakan NIM untuk mahasiswa atau NIP untuk dosen</small>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Nama sesuai KTM/KTP" 
                           value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Kampus <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="nama@uin-alauddin.ac.id" 
                           value="{{ old('email') }}" required>
                </div>
                <small class="text-muted">Harus menggunakan email @uin-alauddin.ac.id</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Minimal 8 karakter" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
                           placeholder="Ulangi password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-register w-100 mb-3">
                <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
            </button>

            <div class="text-center">
                <p class="mb-0">Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--uin-green);">Login di sini</a></p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>