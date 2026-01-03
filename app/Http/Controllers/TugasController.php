<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Kelas;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TugasController extends Controller
{
    // Mahasiswa - View all tugas for a class
    public function index($kelasId)
    {
        $kelas = Kelas::with(['mataKuliah', 'dosen'])->findOrFail($kelasId);
        
        // Check enrollment
        if (Auth::user()->isMahasiswa()) {
            $enrolled = Auth::user()->enrollments()->where('kelas_id', $kelasId)->exists();
            if (!$enrolled) {
                abort(403, 'Anda tidak terdaftar di kelas ini');
            }
        }
        
        $tugas = Tugas::where('kelas_id', $kelasId)
            ->where('status', 'published')
            ->orderBy('deadline', 'desc')
            ->paginate(10);
        
        return view('tugas.index', compact('kelas', 'tugas'));
    }

    // Detail tugas
    public function show($id)
    {
        $tugas = Tugas::with(['kelas.mataKuliah', 'kelas.dosen'])->findOrFail($id);
        
        $submission = null;
        if (Auth::user()->isMahasiswa()) {
            $submission = $tugas->submissions()
                ->where('mahasiswa_id', Auth::id())
                ->latest('attempt')
                ->first();
        }
        
        $submissions = null;
        if (Auth::user()->isDosen()) {
            $submissions = $tugas->submissions()
                ->with(['mahasiswa', 'komentar.user'])
                ->orderBy('submitted_at', 'desc')
                ->get();
        }
        
        return view('tugas.show', compact('tugas', 'submission', 'submissions'));
    }

    // Dosen - Create tugas form
    public function create($kelasId)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('id', $kelasId)
            ->where('dosen_id', Auth::id())
            ->with('mataKuliah')
            ->firstOrFail();
        
        return view('tugas.create', compact('kelas'));
    }

    // Dosen - Store tugas
    public function store(Request $request, $kelasId)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $kelas = Kelas::where('id', $kelasId)
            ->where('dosen_id', Auth::id())
            ->firstOrFail();
        
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tipe' => 'required|in:individu,kelompok',
            'deadline' => 'required|date|after:now',
            'bobot' => 'required|integer|min:1|max:100',
            'file_lampiran' => 'nullable|file|max:10240', // 10MB
            'allow_late_submission' => 'boolean',
            'late_penalty' => 'nullable|integer|min:0|max:100',
        ]);
        
        $filePath = null;
        if ($request->hasFile('file_lampiran')) {
            $filePath = $request->file('file_lampiran')->store('tugas-files', 'public');
        }
        
        $tugas = Tugas::create([
            'kelas_id' => $kelasId,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'tipe' => $validated['tipe'],
            'deadline' => $validated['deadline'],
            'bobot' => $validated['bobot'],
            'file_lampiran' => $filePath,
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'late_penalty' => $validated['late_penalty'] ?? 0,
            'status' => 'published',
        ]);
        
        // Send notifications to all students
        $this->sendTugasNotification($tugas);
        
        return redirect()->route('kelas.show', $kelasId)
            ->with('success', 'Tugas berhasil dibuat!');
    }

    // Dosen - Edit tugas form
    public function edit($id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $tugas = Tugas::with('kelas')->findOrFail($id);
        
        if ($tugas->kelas->dosen_id !== Auth::id()) {
            abort(403);
        }
        
        return view('tugas.edit', compact('tugas'));
    }

    // Dosen - Update tugas
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $tugas = Tugas::findOrFail($id);
        
        if ($tugas->kelas->dosen_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tipe' => 'required|in:individu,kelompok',
            'deadline' => 'required|date',
            'bobot' => 'required|integer|min:1|max:100',
            'file_lampiran' => 'nullable|file|max:10240',
            'allow_late_submission' => 'boolean',
            'late_penalty' => 'nullable|integer|min:0|max:100',
            'status' => 'required|in:draft,published,closed',
        ]);
        
        if ($request->hasFile('file_lampiran')) {
            if ($tugas->file_lampiran) {
                Storage::disk('public')->delete($tugas->file_lampiran);
            }
            $validated['file_lampiran'] = $request->file('file_lampiran')->store('tugas-files', 'public');
        }
        
        $tugas->update($validated);
        
        return redirect()->route('tugas.show', $id)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    // Dosen - Delete tugas
    public function destroy($id)
    {
        if (!Auth::user()->isDosen()) {
            abort(403);
        }
        
        $tugas = Tugas::findOrFail($id);
        
        if ($tugas->kelas->dosen_id !== Auth::id()) {
            abort(403);
        }
        
        $kelasId = $tugas->kelas_id;
        
        if ($tugas->file_lampiran) {
            Storage::disk('public')->delete($tugas->file_lampiran);
        }
        
        $tugas->delete();
        
        return redirect()->route('kelas.show', $kelasId)
            ->with('success', 'Tugas berhasil dihapus!');
    }

    // Send notification to all students in class
    private function sendTugasNotification($tugas)
    {
        $mahasiswaIds = $tugas->kelas->enrollments()->pluck('mahasiswa_id');
        
        foreach ($mahasiswaIds as $mahasiswaId) {
            Notification::create([
                'user_id' => $mahasiswaId,
                'title' => 'Tugas Baru: ' . $tugas->judul,
                'message' => 'Tugas baru telah ditambahkan untuk ' . $tugas->kelas->mataKuliah->nama_mk . '. Deadline: ' . $tugas->deadline->format('d M Y H:i'),
                'type' => 'deadline',
                'link' => route('tugas.show', $tugas->id),
            ]);
        }
    }

    // Menampilkan semua tugas dari seluruh kelas yang diikuti
    // ==========================================
    // METHOD BARU: Tugas Saya (Mahasiswa) - Updated
    // ==========================================
    public function myTasks()
    {
        $user = Auth::user();
        
        // 1. Ambil ID semua kelas yang diikuti mahasiswa
        $kelasIds = $user->enrollments()->pluck('kelas_id');

        // 2. Base Query untuk semua tugas di kelas tersebut
        $queryTugas = Tugas::whereIn('kelas_id', $kelasIds)
            ->where('status', 'published');

        // 3. Hitung Statistik untuk Kartu Atas (Seperti Dashboard Dosen)
        $stats = [
            'total' => $queryTugas->count(),
            'selesai' => (clone $queryTugas)->whereHas('submissions', function($q) use ($user) {
                $q->where('mahasiswa_id', $user->id);
            })->count(),
            'pending' => 0, // Dihitung di bawah
            'terlambat' => 0 // Dihitung di bawah
        ];

        // Hitung pending (Belum submit & Deadline masih ada)
        // Hitung terlambat (Belum submit & Deadline lewat)
        $allTasks = (clone $queryTugas)->get();
        foreach($allTasks as $t) {
            $hasSubmission = $t->submissions()->where('mahasiswa_id', $user->id)->exists();
            if (!$hasSubmission) {
                if ($t->deadline->isFuture()) {
                    $stats['pending']++;
                } else {
                    $stats['terlambat']++;
                }
            }
        }

        // 4. Ambil data tugas untuk tabel (Paginate)
        $tugas = $queryTugas->with(['kelas.mataKuliah', 'kelas.dosen', 'submissions' => function($q) use ($user) {
                $q->where('mahasiswa_id', $user->id);
            }])
            ->orderBy('deadline', 'asc') // Deadline terdekat di atas
            ->paginate(10); 

        return view('mahasiswa.tugas.index', compact('tugas', 'stats'));
    }
}