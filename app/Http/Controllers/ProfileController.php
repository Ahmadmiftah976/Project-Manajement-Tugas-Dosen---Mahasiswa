<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB; // Tambahkan ini agar DB::table bisa jalan

class ProfileController extends Controller
{
   public function show()
    {
        $user = Auth::user();
        
        $stats = [];
        
        if ($user->isMahasiswa()) {
            $stats = [
                'total_kelas' => $user->enrollments()->count(),
                'total_submission' => $user->submissions()->count(),
                
                // --- PERBAIKAN DI SINI (Ganti 'tugas_diterima' jadi 'tugas_selesai') ---
                'tugas_selesai' => $user->submissions()->where('status_revisi', 'diterima')->count(),
                // -----------------------------------------------------------------------

                'rata_rata_nilai' => $user->submissions()->whereNotNull('nilai')->avg('nilai'),
                
                'total_tugas' => DB::table('tugas')
                    ->join('enrollment', 'tugas.kelas_id', '=', 'enrollment.kelas_id')
                    ->where('enrollment.mahasiswa_id', $user->id)
                    ->where('tugas.status', 'published')
                    ->count(),
            ];
        } elseif ($user->isDosen()) {
            $stats = [
                'total_kelas' => $user->kelasAsDoson()->count(),
                'total_mahasiswa' => DB::table('enrollment')
                    ->join('kelas', 'enrollment.kelas_id', '=', 'kelas.id')
                    ->where('kelas.dosen_id', $user->id)
                    ->distinct('enrollment.mahasiswa_id')
                    ->count(),
                'total_tugas' => DB::table('tugas')
                    ->join('kelas', 'tugas.kelas_id', '=', 'kelas.id')
                    ->where('kelas.dosen_id', $user->id)
                    ->count(),
                'pending_grading' => DB::table('submission')
                    ->join('tugas', 'submission.tugas_id', '=', 'tugas.id')
                    ->join('kelas', 'tugas.kelas_id', '=', 'kelas.id')
                    ->where('kelas.dosen_id', $user->id)
                    ->where('submission.status_revisi', 'pending')
                    ->count(),
            ];
        }
        
        return view('profile.show', compact('user', 'stats'));
    }

    // Update profile information
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        
        $user->update($validated);
        
        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    // Update password
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        $user = Auth::user();
        
        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai']);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        
        return back()->with('success', 'Password berhasil diperbarui!');
    }

    // Delete avatar
    public function deleteAvatar()
    {
        $user = Auth::user();
        
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        
        return back()->with('success', 'Foto profil berhasil dihapus!');
    }

    // Show other user profile (public view)
    public function showUser($id)
    {
        $user = User::findOrFail($id);
        
        // Check if viewer has permission to view this profile
        $viewer = Auth::user();
        
        // Mahasiswa can only view dosen profiles
        if ($viewer->isMahasiswa() && !$user->isDosen()) {
            abort(403, 'Anda tidak memiliki akses ke profil ini');
        }
        
        // Dosen can view mahasiswa in their class
        if ($viewer->isDosen() && $user->isMahasiswa()) {
            $hasAccess = DB::table('enrollment')
                ->join('kelas', 'enrollment.kelas_id', '=', 'kelas.id')
                ->where('kelas.dosen_id', $viewer->id)
                ->where('enrollment.mahasiswa_id', $user->id)
                ->exists();
            
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke profil mahasiswa ini');
            }
        }
        
        return view('profile.public', compact('user'));
    }

    // Update preferences
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'deadline_reminder' => 'boolean',
            'reminder_days' => 'nullable|integer|min:1|max:7',
        ]);
        
        // Store preferences in user settings or separate table
        // For now, we'll store in session
        session([
            'preferences' => $validated
        ]);
        
        return back()->with('success', 'Preferensi berhasil diperbarui!');
    }
}