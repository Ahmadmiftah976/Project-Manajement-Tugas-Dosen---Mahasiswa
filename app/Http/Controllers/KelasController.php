<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\Enrollment;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    // Show kelas detail
    public function show($id)
    {
        $kelas = Kelas::with(['mataKuliah', 'dosen', 'enrollments.mahasiswa'])->findOrFail($id);
        
        // Authorization check
        $user = Auth::user();
        if ($user->isMahasiswa()) {
            $enrolled = $user->enrollments()->where('kelas_id', $id)->exists();
            if (!$enrolled) {
                abort(403, 'Anda tidak terdaftar di kelas ini');
            }
        } elseif ($user->isDosen()) {
            if ($kelas->dosen_id !== $user->id) {
                abort(403, 'Anda tidak mengajar kelas ini');
            }
        }
        
        // Get tugas
        $tugas = Tugas::where('kelas_id', $id)
            ->where('status', 'published')
            ->orderBy('deadline', 'desc')
            ->paginate(10);
        
        // Get statistics
        $stats = [
            'total_tugas' => Tugas::where('kelas_id', $id)->where('status', 'published')->count(),
            'total_mahasiswa' => $kelas->enrollments()->count(),
        ];
        
        if ($user->isDosen()) {
            $stats['pending_submissions'] = DB::table('submission')
                ->join('tugas', 'submission.tugas_id', '=', 'tugas.id')
                ->where('tugas.kelas_id', $id)
                ->where('submission.status_revisi', 'pending')
                ->count();
        }
        
        return view('kelas.show', compact('kelas', 'tugas', 'stats'));
    }

    // Dosen - Index all classes taught
    public function index()
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Auth::user()->kelasAsDoson()
            ->with(['mataKuliah', 'enrollments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('kelas.index', compact('kelas'));
    }

    // Dosen - Create kelas form
    public function create()
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $mataKuliah = MataKuliah::where('prodi', Auth::user()->prodi)
            ->orderBy('nama_mk')
            ->get();
        
        return view('kelas.create', compact('mataKuliah'));
    }

    // Dosen - Store kelas
    public function store(Request $request)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'nama_kelas' => 'required|string|max:10',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
            'ruangan' => 'nullable|string|max:100',
            'jadwal' => 'nullable|string',
            'kapasitas' => 'required|integer|min:1|max:100',
        ]);
        
        $validated['dosen_id'] = Auth::id();
        $validated['is_active'] = true;
        
        $kelas = Kelas::create($validated);
        
        return redirect()->route('kelas.show', $kelas->id)
            ->with('success', 'Kelas berhasil dibuat!');
    }

    // Dosen - Edit kelas
    public function edit($id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('dosen_id', Auth::id())->findOrFail($id);
        
        $mataKuliah = MataKuliah::where('prodi', Auth::user()->prodi)
            ->orderBy('nama_mk')
            ->get();
        
        return view('kelas.edit', compact('kelas', 'mataKuliah'));
    }

    // Dosen - Update kelas
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('dosen_id', Auth::id())->findOrFail($id);
        
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'nama_kelas' => 'required|string|max:10',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
            'ruangan' => 'nullable|string|max:100',
            'jadwal' => 'nullable|string',
            'kapasitas' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);
        
        $kelas->update($validated);
        
        return redirect()->route('kelas.show', $id)
            ->with('success', 'Kelas berhasil diperbarui!');
    }

    // Dosen - Delete kelas
    public function destroy($id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('dosen_id', Auth::id())->findOrFail($id);
        
        // Check if there are any tugas
        if ($kelas->tugas()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kelas yang memiliki tugas!');
        }
        
        $kelas->delete();
        
        return redirect()->route('dosen.kelas.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }

    // Dosen - Manage enrollments
    public function manageEnrollments($id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('dosen_id', Auth::id())
            ->with(['enrollments.mahasiswa', 'mataKuliah'])
            ->findOrFail($id);
        
        // Get available mahasiswa (same prodi, not enrolled)
        $availableMahasiswa = User::where('role', 'mahasiswa')
            ->where('prodi', $kelas->mataKuliah->prodi)
            ->whereNotIn('id', $kelas->enrollments->pluck('mahasiswa_id'))
            ->orderBy('name')
            ->get();
        
        return view('kelas.enrollments', compact('kelas', 'availableMahasiswa'));
    }

    // Dosen - Add mahasiswa to kelas
    public function addMahasiswa(Request $request, $id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('dosen_id', Auth::id())->findOrFail($id);
        
        $validated = $request->validate([
            'mahasiswa_id' => 'required|exists:users,id',
        ]);
        
        // Check if already enrolled
        $exists = Enrollment::where('kelas_id', $id)
            ->where('mahasiswa_id', $validated['mahasiswa_id'])
            ->exists();
        
        if ($exists) {
            return back()->with('error', 'Mahasiswa sudah terdaftar di kelas ini!');
        }
        
        // Check capacity
        if ($kelas->enrollments()->count() >= $kelas->kapasitas) {
            return back()->with('error', 'Kelas sudah penuh!');
        }
        
        Enrollment::create([
            'kelas_id' => $id,
            'mahasiswa_id' => $validated['mahasiswa_id'],
            'status' => 'active',
        ]);
        
        return back()->with('success', 'Mahasiswa berhasil ditambahkan ke kelas!');
    }

    // Dosen - Remove mahasiswa from kelas
    public function removeMahasiswa($kelasId, $mahasiswaId)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('dosen_id', Auth::id())->findOrFail($kelasId);
        
        $enrollment = Enrollment::where('kelas_id', $kelasId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->firstOrFail();
        
        $enrollment->delete();
        
        return back()->with('success', 'Mahasiswa berhasil dihapus dari kelas!');
    }

    // Get kelas by mata kuliah (AJAX)
    public function getByMataKuliah($mataKuliahId)
    {
        $kelas = Kelas::where('mata_kuliah_id', $mataKuliahId)
            ->where('is_active', true)
            ->with('dosen:id,name')
            ->get();
        
        return response()->json($kelas);
    }

    // Menampilkan daftar kelas yang diikuti mahasiswa saat ini
    public function myClasses()
    {
        $enrollments = Auth::user()->enrollments()
            ->with(['kelas.mataKuliah', 'kelas.dosen'])
            ->get();
            
        return view('mahasiswa.kelas.index', compact('enrollments'));
    }
}