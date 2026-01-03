<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\Submission;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen');
    }

    // Dashboard dosen (alternative)
    public function index()
    {
        return redirect()->route('dashboard');
    }

    // Show all classes taught by dosen
    public function myClasses()
    {
        $kelas = Auth::user()->kelasAsDoson()
            ->with(['mataKuliah', 'enrollments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('kelas.index', compact('kelas'));
    }

    // Show all tugas created by dosen
    public function myTugas(Request $request)
    {
        $kelasIds = Auth::user()->kelasAsDoson()->pluck('id');
        
        $query = Tugas::whereIn('kelas_id', $kelasIds)
            ->with(['kelas.mataKuliah']);
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $tugas = $query->paginate(15);
        
        return view('dosen.tugas', compact('tugas'));
    }

    // Show all submissions that need grading
    public function submissions(Request $request)
    {
        $kelasIds = Auth::user()->kelasAsDoson()->pluck('id');
        
        $query = Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->with(['tugas.kelas.mataKuliah', 'mahasiswa', 'komentar']);
        
        // Filter by status
        $status = $request->get('status', 'pending');
        if ($status != 'all') {
            $query->where('status_revisi', $status);
        }
        
        // Sort
        $query->orderBy('submitted_at', 'desc');
        
        $submissions = $query->paginate(20);
        
        // Calculate statistics
        $stats = [
            'total' => Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->count(),
            'pending' => Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->where('status_revisi', 'pending')->count(),
            'diterima' => Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->where('status_revisi', 'diterima')->count(),
            'revisi' => Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->where('status_revisi', 'revisi')->count(),
        ];
        
        return view('dosen.submissions', compact('submissions', 'stats'));
    }

    // Batch grading
    public function batchGrading($tugasId)
    {
        $tugas = Tugas::with(['kelas.mataKuliah'])->findOrFail($tugasId);
        
        // Check authorization
        if ($tugas->kelas->dosen_id !== Auth::id()) {
            abort(403);
        }
        
        $submissions = Submission::where('tugas_id', $tugasId)
            ->with('mahasiswa')
            ->orderBy('submitted_at', 'asc')
            ->get();
        
        return view('dosen.batch-grading', compact('tugas', 'submissions'));
    }

    // Store batch grades
    public function storeBatchGrades(Request $request, $tugasId)
    {
        $tugas = Tugas::findOrFail($tugasId);
        
        // Check authorization
        if ($tugas->kelas->dosen_id !== Auth::id()) {
            abort(403);
        }
        
        $grades = $request->input('grades', []);
        
        foreach ($grades as $submissionId => $gradeData) {
            $submission = Submission::where('tugas_id', $tugasId)
                ->where('id', $submissionId)
                ->first();
            
            if ($submission && isset($gradeData['nilai']) && isset($gradeData['status'])) {
                $submission->update([
                    'nilai' => $gradeData['nilai'],
                    'status_revisi' => $gradeData['status'],
                ]);
                
                // Send notification
                \App\Models\Notification::create([
                    'user_id' => $submission->mahasiswa_id,
                    'title' => 'Update Nilai',
                    'message' => 'Tugas "' . $tugas->judul . '" telah dinilai dengan nilai ' . $gradeData['nilai'],
                    'type' => 'grade',
                    'link' => route('tugas.show', $tugasId),
                ]);
            }
        }
        
        return redirect()->route('tugas.show', $tugasId)
            ->with('success', 'Penilaian batch berhasil disimpan!');
    }

    // Show mahasiswa list in a kelas
    public function mahasiswaList($kelasId)
    {
        $kelas = Kelas::where('dosen_id', Auth::id())
            ->with(['mataKuliah', 'enrollments.mahasiswa'])
            ->findOrFail($kelasId);
        
        // Get submissions statistics for each mahasiswa
        $tugasIds = Tugas::where('kelas_id', $kelasId)->pluck('id');
        
        $mahasiswaStats = [];
        foreach ($kelas->enrollments as $enrollment) {
            $mahasiswa = $enrollment->mahasiswa;
            
            $stats = [
                'total_tugas' => $tugasIds->count(),
                'submitted' => Submission::whereIn('tugas_id', $tugasIds)
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->count(),
                'diterima' => Submission::whereIn('tugas_id', $tugasIds)
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('status_revisi', 'diterima')
                    ->count(),
                'rata_nilai' => Submission::whereIn('tugas_id', $tugasIds)
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->whereNotNull('nilai')
                    ->avg('nilai'),
            ];
            
            $mahasiswaStats[$mahasiswa->id] = $stats;
        }
        
        return view('dosen.mahasiswa-list', compact('kelas', 'mahasiswaStats'));
    }

    // Show individual mahasiswa detail
    public function mahasiswaDetail($kelasId, $mahasiswaId)
    {
        $kelas = Kelas::where('dosen_id', Auth::id())
            ->with('mataKuliah')
            ->findOrFail($kelasId);
        
        $mahasiswa = User::findOrFail($mahasiswaId);
        
        // Check if mahasiswa is enrolled
        $enrolled = Enrollment::where('kelas_id', $kelasId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->exists();
        
        if (!$enrolled) {
            abort(403, 'Mahasiswa tidak terdaftar di kelas ini');
        }
        
        // Get all submissions for this mahasiswa in this kelas
        $submissions = Submission::whereHas('tugas', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })
            ->where('mahasiswa_id', $mahasiswaId)
            ->with(['tugas', 'komentar'])
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        return view('dosen.mahasiswa-detail', compact('kelas', 'mahasiswa', 'submissions'));
    }

    // Statistics and reports
    public function statistics()
    {
        $user = Auth::user();
        $kelasIds = $user->kelasAsDoson()->pluck('id');
        
        // Overall statistics
        $stats = [
            'total_kelas' => $user->kelasAsDoson()->count(),
            'total_mahasiswa' => Enrollment::whereIn('kelas_id', $kelasIds)
                ->distinct('mahasiswa_id')
                ->count(),
            'total_tugas' => Tugas::whereIn('kelas_id', $kelasIds)->count(),
            'total_submission' => Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->count(),
            'pending_grading' => Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->where('status_revisi', 'pending')->count(),
            'rata_rata_nilai' => round(Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })->whereNotNull('nilai')->avg('nilai'), 2),
        ];
        
        // Submission rate by kelas
        $kelasSubmissionRate = DB::table('kelas')
            ->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->leftJoin('tugas', 'kelas.id', '=', 'tugas.kelas_id')
            ->leftJoin('enrollment', 'kelas.id', '=', 'enrollment.kelas_id')
            ->where('kelas.dosen_id', $user->id)
            ->select(
                'mata_kuliah.nama_mk',
                DB::raw('COUNT(DISTINCT tugas.id) as total_tugas'),
                DB::raw('COUNT(DISTINCT enrollment.mahasiswa_id) as total_mahasiswa')
            )
            ->groupBy('kelas.id', 'mata_kuliah.nama_mk')
            ->get();
        
        // Monthly submission trend
        $monthlyTrend = Submission::whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->selectRaw('MONTH(submitted_at) as month, COUNT(*) as count')
            ->whereYear('submitted_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');
        
        return view('dosen.statistics', compact(
            'stats',
            'kelasSubmissionRate',
            'monthlyTrend'
        ));
    }

    // Export report
    public function exportReport($kelasId)
    {
        $kelas = Kelas::where('dosen_id', Auth::id())
            ->with(['mataKuliah', 'enrollments.mahasiswa'])
            ->findOrFail($kelasId);
        
        $tugasIds = Tugas::where('kelas_id', $kelasId)->pluck('id');
        
        // Get all submissions
        $submissions = Submission::whereIn('tugas_id', $tugasIds)
            ->with(['tugas', 'mahasiswa'])
            ->orderBy('mahasiswa_id')
            ->orderBy('submitted_at')
            ->get();
        
        // Generate PDF
        $pdf = \PDF::loadView('dosen.report-pdf', compact('kelas', 'submissions'));
        
        return $pdf->download('report-' . $kelas->mataKuliah->kode_mk . '-' . $kelas->nama_kelas . '.pdf');
    }
}