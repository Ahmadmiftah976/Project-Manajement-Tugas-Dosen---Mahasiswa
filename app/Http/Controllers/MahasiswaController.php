<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:mahasiswa');
    }

    // Dashboard mahasiswa (alternative to DashboardController)
    public function index()
    {
        return redirect()->route('dashboard');
    }

    // Show all enrolled classes
    public function myClasses()
    {
        $enrollments = Auth::user()->enrollments()
            ->with(['kelas.mataKuliah', 'kelas.dosen'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('mahasiswa.classes', compact('enrollments'));
    }

    // Show all tugas
    public function myTugas(Request $request)
    {
        $user = Auth::user();
        $kelasIds = $user->enrollments()->pluck('kelas_id');
        
        $query = Tugas::whereIn('kelas_id', $kelasIds)
            ->where('status', 'published')
            ->with(['kelas.mataKuliah']);
        
        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->where('deadline', '>', now());
                    break;
                case 'overdue':
                    $query->where('deadline', '<', now())
                          ->whereDoesntHave('submissions', function($q) use ($user) {
                              $q->where('mahasiswa_id', $user->id)
                                ->where('status_revisi', '!=', 'ditolak');
                          });
                    break;
                case 'submitted':
                    $query->whereHas('submissions', function($q) use ($user) {
                        $q->where('mahasiswa_id', $user->id);
                    });
                    break;
            }
        }
        
        // Sort
        $sortBy = $request->get('sort', 'deadline');
        if ($sortBy === 'deadline') {
            $query->orderBy('deadline', 'asc');
        } elseif ($sortBy === 'created') {
            $query->orderBy('created_at', 'desc');
        }
        
        $tugas = $query->paginate(15);
        
        return view('mahasiswa.tugas', compact('tugas'));
    }

    // Show all submissions
    public function mySubmissions(Request $request)
    {
        $query = Auth::user()->submissions()
            ->with(['tugas.kelas.mataKuliah', 'komentar'])
            ->orderBy('submitted_at', 'desc');
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status_revisi', $request->status);
        }
        
        $submissions = $query->paginate(15);
        
        // Calculate statistics
        $stats = [
            'total' => Auth::user()->submissions()->count(),
            'pending' => Auth::user()->submissions()->where('status_revisi', 'pending')->count(),
            'diterima' => Auth::user()->submissions()->where('status_revisi', 'diterima')->count(),
            'revisi' => Auth::user()->submissions()->where('status_revisi', 'revisi')->count(),
            'ditolak' => Auth::user()->submissions()->where('status_revisi', 'ditolak')->count(),
        ];
        
        return view('mahasiswa.submissions', compact('submissions', 'stats'));
    }

    // Show grades/nilai
    public function myGrades()
    {
        $user = Auth::user();
        
        // Get all submissions with grades grouped by kelas
        $submissions = Submission::where('mahasiswa_id', $user->id)
            ->whereNotNull('nilai')
            ->with(['tugas.kelas.mataKuliah'])
            ->get()
            ->groupBy('tugas.kelas_id');
        
        // Calculate average per kelas
        $kelasGrades = [];
        foreach ($submissions as $kelasId => $kelasSubmissions) {
            $kelas = $kelasSubmissions->first()->tugas->kelas;
            $kelasGrades[] = [
                'kelas' => $kelas,
                'submissions' => $kelasSubmissions,
                'average' => $kelasSubmissions->avg('nilai'),
                'total_tugas' => Tugas::where('kelas_id', $kelasId)->count(),
                'graded_tugas' => $kelasSubmissions->count(),
            ];
        }
        
        // Overall average
        $overallAverage = Submission::where('mahasiswa_id', $user->id)
            ->whereNotNull('nilai')
            ->avg('nilai');
        
        return view('mahasiswa.grades', compact('kelasGrades', 'overallAverage'));
    }

    // Show calendar view
    public function calendar()
    {
        $user = Auth::user();
        $kelasIds = $user->enrollments()->pluck('kelas_id');
        
        // Get all tugas with deadlines
        $tugas = Tugas::whereIn('kelas_id', $kelasIds)
            ->where('status', 'published')
            ->where('deadline', '>=', now()->startOfMonth())
            ->where('deadline', '<=', now()->endOfMonth()->addMonth())
            ->with(['kelas.mataKuliah'])
            ->get();
        
        // Format for calendar
        $events = $tugas->map(function($t) use ($user) {
            $submitted = $t->submissions()
                ->where('mahasiswa_id', $user->id)
                ->exists();
            
            return [
                'id' => $t->id,
                'title' => $t->judul,
                'start' => $t->deadline->format('Y-m-d H:i:s'),
                'className' => $submitted ? 'event-success' : 'event-warning',
                'url' => route('tugas.show', $t->id),
                'extendedProps' => [
                    'matakuliah' => $t->kelas->mataKuliah->nama_mk,
                    'submitted' => $submitted,
                ],
            ];
        });
        
        return view('mahasiswa.calendar', compact('events'));
    }

    // Show statistics
    public function statistics()
    {
        $user = Auth::user();
        
        // Overall statistics
        $stats = [
            'total_kelas' => $user->enrollments()->count(),
            'total_tugas' => Tugas::whereIn('kelas_id', $user->enrollments()->pluck('kelas_id'))
                ->where('status', 'published')
                ->count(),
            'total_submission' => $user->submissions()->count(),
            'tugas_diterima' => $user->submissions()->where('status_revisi', 'diterima')->count(),
            'tugas_pending' => $user->submissions()->where('status_revisi', 'pending')->count(),
            'tugas_revisi' => $user->submissions()->where('status_revisi', 'revisi')->count(),
            'rata_rata_nilai' => round($user->submissions()->whereNotNull('nilai')->avg('nilai'), 2),
            'submission_rate' => 0,
        ];
        
        if ($stats['total_tugas'] > 0) {
            $stats['submission_rate'] = round(($stats['total_submission'] / $stats['total_tugas']) * 100, 1);
        }
        
        // Monthly submission trend
        $monthlyTrend = Submission::where('mahasiswa_id', $user->id)
            ->selectRaw('MONTH(submitted_at) as month, COUNT(*) as count')
            ->whereYear('submitted_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');
        
        // Status distribution
        $statusDistribution = Submission::where('mahasiswa_id', $user->id)
            ->selectRaw('status_revisi, COUNT(*) as count')
            ->groupBy('status_revisi')
            ->get()
            ->pluck('count', 'status_revisi');
        
        // Per kelas performance
        $kelasPerformance = DB::table('submission')
            ->join('tugas', 'submission.tugas_id', '=', 'tugas.id')
            ->join('kelas', 'tugas.kelas_id', '=', 'kelas.id')
            ->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->where('submission.mahasiswa_id', $user->id)
            ->whereNotNull('submission.nilai')
            ->select('mata_kuliah.nama_mk', DB::raw('AVG(submission.nilai) as avg_nilai'))
            ->groupBy('mata_kuliah.id', 'mata_kuliah.nama_mk')
            ->get();
        
        return view('mahasiswa.statistics', compact(
            'stats',
            'monthlyTrend',
            'statusDistribution',
            'kelasPerformance'
        ));
    }

    // Export transcript/report
    public function exportTranscript()
    {
        $user = Auth::user();
        
        // Get all graded submissions
        $submissions = Submission::where('mahasiswa_id', $user->id)
            ->whereNotNull('nilai')
            ->with(['tugas.kelas.mataKuliah'])
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        // Generate PDF using dompdf
        $pdf = \PDF::loadView('mahasiswa.transcript-pdf', compact('user', 'submissions'));
        
        return $pdf->download('transcript-' . $user->nim_nip . '.pdf');
    }
}