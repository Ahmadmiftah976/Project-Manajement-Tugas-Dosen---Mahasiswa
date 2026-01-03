<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\Submission;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    // Admin Dashboard
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'total_dosen' => User::where('role', 'dosen')->count(),
            'total_matakuliah' => MataKuliah::count(),
            'total_kelas' => Kelas::count(),
            'total_tugas' => Tugas::count(),
            'total_submission' => Submission::count(),
            'active_kelas' => Kelas::where('is_active', true)->count(),
        ];
        
        // Recent activities
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentKelas = Kelas::with(['mataKuliah', 'dosen'])->orderBy('created_at', 'desc')->take(5)->get();
        
        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentKelas'));
    }

    // ========== USER MANAGEMENT ==========
    
    // List all users
    public function users(Request $request)
    {
        $query = User::query();
        
        // Filter by role
        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    // Create user form
    public function createUser()
    {
        return view('admin.users.create');
    }

    // Store user
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'nim_nip' => 'required|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|ends_with:@uin-alauddin.ac.id',
            'password' => 'required|min:8',
            'role' => 'required|in:mahasiswa,dosen,admin',
            'prodi' => 'nullable|string',
            'fakultas' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat!');
    }

    // Edit user form
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // Update user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'nim_nip' => 'required|unique:users,nim_nip,' . $id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id . '|ends_with:@uin-alauddin.ac.id',
            'role' => 'required|in:mahasiswa,dosen,admin',
            'prodi' => 'nullable|string',
            'fakultas' => 'nullable|string',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Only update password if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        
        $user->update($validated);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    // Delete user
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting self
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri!');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus!');
    }

    // ========== MATA KULIAH MANAGEMENT ==========
    
    // List mata kuliah
    public function mataKuliah(Request $request)
    {
        $query = MataKuliah::query();
        
        // Filter by prodi
        if ($request->has('prodi') && $request->prodi != 'all') {
            $query->where('prodi', $request->prodi);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                  ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }
        
        $mataKuliah = $query->orderBy('nama_mk')->paginate(20);
        
        // Get unique prodi for filter
        $prodis = MataKuliah::distinct()->pluck('prodi');
        
        return view('admin.matakuliah.index', compact('mataKuliah', 'prodis'));
    }

    // Create mata kuliah form
    public function createMataKuliah()
    {
        return view('admin.matakuliah.create');
    }

    // Store mata kuliah
    public function storeMataKuliah(Request $request)
    {
        $validated = $request->validate([
            'kode_mk' => 'required|unique:mata_kuliah',
            'nama_mk' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:6',
            'semester' => 'required|string',
            'prodi' => 'required|string',
            'fakultas' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);
        
        MataKuliah::create($validated);
        
        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil dibuat!');
    }

    // Edit mata kuliah form
    public function editMataKuliah($id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        return view('admin.matakuliah.edit', compact('mataKuliah'));
    }

    // Update mata kuliah
    public function updateMataKuliah(Request $request, $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        
        $validated = $request->validate([
            'kode_mk' => 'required|unique:mata_kuliah,kode_mk,' . $id,
            'nama_mk' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:6',
            'semester' => 'required|string',
            'prodi' => 'required|string',
            'fakultas' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);
        
        $mataKuliah->update($validated);
        
        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil diperbarui!');
    }

    // Delete mata kuliah
    public function deleteMataKuliah($id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        
        // Check if has kelas
        if ($mataKuliah->kelas()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus mata kuliah yang memiliki kelas!');
        }
        
        $mataKuliah->delete();
        
        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus!');
    }

    // ========== KELAS MANAGEMENT ==========
    
    // List kelas
    public function kelas(Request $request)
    {
        $query = Kelas::with(['mataKuliah', 'dosen']);
        
        // Filter
        if ($request->has('tahun_ajaran') && $request->tahun_ajaran != 'all') {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }
        
        if ($request->has('semester') && $request->semester != 'all') {
            $query->where('semester', $request->semester);
        }
        
        $kelas = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get unique tahun ajaran for filter
        $tahunAjarans = Kelas::distinct()->pluck('tahun_ajaran');
        
        return view('admin.kelas.index', compact('kelas', 'tahunAjarans'));
    }

    // ========== REPORTS & ANALYTICS ==========
    
    // System reports
    public function reports()
    {
        // Enrollment statistics
        $enrollmentStats = DB::table('enrollment')
            ->join('kelas', 'enrollment.kelas_id', '=', 'kelas.id')
            ->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->select('mata_kuliah.nama_mk', DB::raw('COUNT(*) as total'))
            ->groupBy('mata_kuliah.id', 'mata_kuliah.nama_mk')
            ->orderBy('total', 'desc')
            ->get();
        
        // Submission statistics
        $submissionStats = Submission::selectRaw('status_revisi, COUNT(*) as count')
            ->groupBy('status_revisi')
            ->get()
            ->pluck('count', 'status_revisi');
        
        // Monthly growth
        $monthlyGrowth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');
        
        // Top performing mahasiswa
        $topMahasiswa = DB::table('users')
            ->join('submission', 'users.id', '=', 'submission.mahasiswa_id')
            ->select('users.name', 'users.nim_nip', DB::raw('AVG(submission.nilai) as avg_nilai'))
            ->where('users.role', 'mahasiswa')
            ->whereNotNull('submission.nilai')
            ->groupBy('users.id', 'users.name', 'users.nim_nip')
            ->orderBy('avg_nilai', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.reports', compact(
            'enrollmentStats',
            'submissionStats',
            'monthlyGrowth',
            'topMahasiswa'
        ));
    }

    // System settings
    public function settings()
    {
        return view('admin.settings');
    }

    // Update settings
    public function updateSettings(Request $request)
    {
        // Implement settings update logic
        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}