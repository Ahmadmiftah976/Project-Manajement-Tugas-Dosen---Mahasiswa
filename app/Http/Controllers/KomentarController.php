<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use App\Models\Submission;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KomentarController extends Controller
{
    // Store komentar
    public function store(Request $request)
    {
        $validated = $request->validate([
            'submission_id' => 'required|exists:submission,id',
            'komentar' => 'required|string',
            'file_lampiran' => 'nullable|file|max:10240', // 10MB
        ]);
        
        $submission = Submission::with(['tugas.kelas', 'mahasiswa'])->findOrFail($validated['submission_id']);
        
        // Authorization check
        $user = Auth::user();
        if ($user->isMahasiswa() && $submission->mahasiswa_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke submission ini');
        }
        
        if ($user->isDosen() && $submission->tugas->kelas->dosen_id !== $user->id) {
            abort(403, 'Anda tidak mengajar kelas ini');
        }
        
        $filePath = null;
        if ($request->hasFile('file_lampiran')) {
            $filePath = $request->file('file_lampiran')->store('komentar-files', 'public');
        }
        
        $komentar = Komentar::create([
            'submission_id' => $validated['submission_id'],
            'user_id' => Auth::id(),
            'komentar' => $validated['komentar'],
            'file_lampiran' => $filePath,
        ]);
        
        // Send notification
        $this->sendKomentarNotification($komentar, $submission);
        
        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }

    // Update komentar
    public function update(Request $request, $id)
    {
        $komentar = Komentar::findOrFail($id);
        
        // Authorization check - only owner can update
        if ($komentar->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah komentar ini');
        }
        
        $validated = $request->validate([
            'komentar' => 'required|string',
            'file_lampiran' => 'nullable|file|max:10240',
        ]);
        
        if ($request->hasFile('file_lampiran')) {
            // Delete old file if exists
            if ($komentar->file_lampiran) {
                Storage::disk('public')->delete($komentar->file_lampiran);
            }
            $validated['file_lampiran'] = $request->file('file_lampiran')->store('komentar-files', 'public');
        }
        
        $komentar->update($validated);
        
        return back()->with('success', 'Komentar berhasil diperbarui!');
    }

    // Delete komentar
    public function destroy($id)
    {
        $komentar = Komentar::findOrFail($id);
        
        // Authorization check - only owner or dosen can delete
        $user = Auth::user();
        $submission = $komentar->submission;
        
        $canDelete = false;
        if ($komentar->user_id === $user->id) {
            $canDelete = true;
        } elseif ($user->isDosen() && $submission->tugas->kelas->dosen_id === $user->id) {
            $canDelete = true;
        }
        
        if (!$canDelete) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus komentar ini');
        }
        
        // Delete file if exists
        if ($komentar->file_lampiran) {
            Storage::disk('public')->delete($komentar->file_lampiran);
        }
        
        $komentar->delete();
        
        return back()->with('success', 'Komentar berhasil dihapus!');
    }

    // Download komentar attachment
    public function downloadAttachment($id)
    {
        $komentar = Komentar::findOrFail($id);
        
        // Authorization check
        $user = Auth::user();
        $submission = $komentar->submission;
        
        $canDownload = false;
        if ($submission->mahasiswa_id === $user->id) {
            $canDownload = true;
        } elseif ($user->isDosen() && $submission->tugas->kelas->dosen_id === $user->id) {
            $canDownload = true;
        }
        
        if (!$canDownload) {
            abort(403, 'Anda tidak memiliki akses ke file ini');
        }
        
        if (!$komentar->file_lampiran) {
            abort(404, 'File tidak ditemukan');
        }
        
        return Storage::disk('public')->download($komentar->file_lampiran);
    }

    // Get komentar for submission (AJAX)
    public function getBySubmission($submissionId)
    {
        $submission = Submission::findOrFail($submissionId);
        
        // Authorization check
        $user = Auth::user();
        if ($user->isMahasiswa() && $submission->mahasiswa_id !== $user->id) {
            abort(403);
        }
        
        if ($user->isDosen() && $submission->tugas->kelas->dosen_id !== $user->id) {
            abort(403);
        }
        
        $komentar = Komentar::where('submission_id', $submissionId)
            ->with('user:id,name,role,avatar')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return response()->json($komentar);
    }

    // Send notification when komentar is added
    private function sendKomentarNotification($komentar, $submission)
    {
        $sender = Auth::user();
        
        // Determine recipient
        if ($sender->isDosen()) {
            // Notify mahasiswa
            $recipientId = $submission->mahasiswa_id;
            $title = 'Komentar Baru dari Dosen';
            $message = $sender->name . ' memberikan komentar pada tugas "' . $submission->tugas->judul . '"';
        } else {
            // Notify dosen
            $recipientId = $submission->tugas->kelas->dosen_id;
            $title = 'Komentar Baru dari Mahasiswa';
            $message = $sender->name . ' memberikan komentar pada submission tugas "' . $submission->tugas->judul . '"';
        }
        
        Notification::create([
            'user_id' => $recipientId,
            'title' => $title,
            'message' => $message,
            'type' => 'comment',
            'link' => route('tugas.show', $submission->tugas_id),
        ]);
    }
}