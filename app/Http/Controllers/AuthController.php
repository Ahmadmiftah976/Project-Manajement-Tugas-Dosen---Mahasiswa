<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nim_nip' => 'required|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validasi email kampus
        if (!str_ends_with($request->email, '@uin-alauddin.ac.id')) {
            return back()->withErrors(['email' => 'Email harus menggunakan domain @uin-alauddin.ac.id'])->withInput();
        }

        // Cek di database kampus (simulasi)
        $userData = $this->checkKampusDatabase($request->nim_nip, $request->email);
        
        if (!$userData) {
            return back()->withErrors(['email' => 'Email atau NIM/NIP tidak terdaftar di database kampus'])->withInput();
        }

        $user = User::create([
            'nim_nip' => $request->nim_nip,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $userData['role'],
            'prodi' => $userData['prodi'] ?? null,
            'fakultas' => $userData['fakultas'] ?? null,
        ]);

        // --- PERUBAHAN ADA DI SINI ---
        
        // 1. Baris ini SAYA HAPUS (supaya tidak auto-login)
        // Auth::login($user); 

        // 2. Redirect saya ubah ke route 'login' (bukan dashboard)
        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login dengan akun baru Anda.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Logout berhasil!');
    }

    // Simulasi pengecekan database kampus
    private function checkKampusDatabase($nim_nip, $email)
    {
        // Dalam implementasi nyata, query ke database kampus
        // Untuk demo, kita simulasikan dengan logika sederhana
        
        // Cek apakah NIM (mahasiswa) atau NIP (dosen)
        if (strlen($nim_nip) > 15) {
            // NIP Dosen (biasanya 18 digit)
            return [
                'role' => 'dosen',
                'prodi' => 'Sistem Informasi',
                'fakultas' => 'Sains dan Teknologi',
            ];
        } else {
            // NIM Mahasiswa
            return [
                'role' => 'mahasiswa',
                'prodi' => 'Sistem Informasi',
                'fakultas' => 'Sains dan Teknologi',
            ];
        }
    }
}