<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\Submission;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isMahasiswa()) {
            return $this->mahasiswaDashboard();
        } elseif ($user->isDosen()) {
            return $this->dosenDashboard();
        } else {
            return $this->adminDashboard();
        }
    }

    private function mahasiswaDashboard()
    {
        $user = Auth::user();
        
        // Get enrolled classes
        $enrollments = $user->enrollments()->with(['kelas.mataKuliah', 'kelas.dosen'])->get();
        $kelasIds = $enrollments->pluck('kelas_id');
        
        // Get upcoming assignments
        $upcomingTugas = Tugas::whereIn('kelas_id', $kelasIds)
            ->where('deadline', '>', Carbon::now())
            ->where('status', 'published')
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->with('kelas.mataKuliah')
            ->get();
        
        // Get overdue assignments
        $overdueTugas = Tugas::whereIn('kelas_id', $kelasIds)
            ->where('deadline', '<', Carbon::now())
            ->where('status', 'published')
            ->whereDoesntHave('submissions', function($query) use ($user) {
                $query->where('mahasiswa_id', $user->id)
                      ->where('status_revisi', '!=', 'ditolak');
            })
            ->with('kelas.mataKuliah')
            ->get();
        
        // Get recent submissions
        $recentSubmissions = Submission::where('mahasiswa_id', $user->id)
            ->with(['tugas.kelas.mataKuliah'])
            ->orderBy('submitted_at', 'desc')
            ->take(5)
            ->get();
        
        // Statistics
        $stats = [
            'total_kelas' => $enrollments->count(),
            'total_tugas' => Tugas::whereIn('kelas_id', $kelasIds)->where('status', 'published')->count(),
            'tugas_selesai' => Submission::where('mahasiswa_id', $user->id)
                                         ->where('status_revisi', 'diterima')
                                         ->count(),
            'tugas_pending' => $overdueTugas->count(),
        ];
        
        // Notifications
        $notifications = $user->unreadNotifications()->orderBy('created_at', 'desc')->take(5)->get();
        
        return view('dashboard.mahasiswa', compact(
            'enrollments',
            'upcomingTugas',
            'overdueTugas',
            'recentSubmissions',
            'stats',
            'notifications'
        ));
    }

    private function dosenDashboard()
    {
        $user = Auth::user();
        
        // Get classes taught by dosen
        $kelas = $user->kelasAsDoson()->with('mataKuliah')->get();
        $kelasIds = $kelas->pluck('id');
        
        // Get assignments
        $tugas = Tugas::whereIn('kelas_id', $kelasIds)
            ->with('kelas.mataKuliah')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get pending submissions (need grading)
        $pendingSubmissions = Submission::whereHas('tugas', function($query) use ($kelasIds) {
                $query->whereIn('kelas_id', $kelasIds);
            })
            ->where('status_revisi', 'pending')
            ->with(['tugas.kelas.mataKuliah', 'mahasiswa'])
            ->orderBy('submitted_at', 'desc')
            ->take(10)
            ->get();
        
        // Statistics
        $stats = [
            'total_kelas' => $kelas->count(),
            'total_tugas' => Tugas::whereIn('kelas_id', $kelasIds)->count(),
            'total_mahasiswa' => \DB::table('enrollment')->whereIn('kelas_id', $kelasIds)->count(),
            'pending_grading' => $pendingSubmissions->count(),
        ];
        
        // Notifications
        $notifications = $user->unreadNotifications()->orderBy('created_at', 'desc')->take(5)->get();
        
        return view('dashboard.dosen', compact(
            'kelas',
            'tugas',
            'pendingSubmissions',
            'stats',
            'notifications'
        ));
    }

    private function adminDashboard()
    {
        // Admin dashboard implementation
        return view('dashboard.admin');
    }
}