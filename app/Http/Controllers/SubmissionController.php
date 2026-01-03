<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Tugas;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubmissionController extends Controller
{
    // Mahasiswa - Submit tugas
    public function store(Request $request, $tugasId)
    {
        // Validasi (Sudah termasuk gambar)
        $request->validate([
            'file_submission' => 'required|file|mimes:pdf,doc,docx,zip,rar,png,jpg,jpeg|max:20480',
            'catatan' => 'nullable|string',
        ]);

        $tugas = Tugas::findOrFail($tugasId);
        $mahasiswaId = Auth::id();

        // 1. Cek apakah sudah ada submission sebelumnya?
        $submission = Submission::where('tugas_id', $tugasId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->first();

        // Proses Upload File
        if ($request->hasFile('file_submission')) {
            // Hapus file lama jika ada (Gunakan nama kolom yang benar)
            if ($submission && $submission->file_submission) {
                Storage::disk('public')->delete($submission->file_submission);
            }
            // Simpan file baru
            $filePath = $request->file('file_submission')->store('submissions', 'public');
        } else {
            return back()->with('error', 'File wajib diupload!');
        }

        $isLate = now()->greaterThan($tugas->deadline);

        if ($submission) {
            // ===============================================
            // UPDATE (REVISI)
            // ===============================================
            $submission->update([
                'file_submission' => $filePath, // <-- PERBAIKAN DISINI (Sebelumnya file_path)
                'catatan' => $request->catatan,
                'submitted_at' => now(),
                'status_revisi' => 'pending',
                'is_late' => $isLate,
            ]);

            $message = 'Tugas berhasil diperbarui (Revisi terkirim)!';

        } else {
            // ===============================================
            // BUAT BARU
            // ===============================================
            Submission::create([
                'tugas_id' => $tugasId,
                'mahasiswa_id' => $mahasiswaId,
                'file_submission' => $filePath, // <-- PERBAIKAN DISINI (Sebelumnya file_path)
                'catatan' => $request->catatan,
                'submitted_at' => now(),
                'status_revisi' => 'pending',
                'nilai' => null,
                'is_late' => $isLate,
            ]);

            $message = 'Tugas berhasil dikumpulkan!';
        }

        return back()->with('success', $message);
    }

    // Dosen - Update status and give grade
    // app/Http/Controllers/SubmissionController.php

    public function updateStatus(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'status_revisi' => 'required|in:diterima,revisi,ditolak',
            'nilai' => 'nullable|numeric|min:0|max:100',
            'komentar' => 'nullable|string', // Pastikan ini ada
        ]);

        // 2. Ambil Data Submission
        $submission = Submission::findOrFail($id);

        // 3. Update Status dan Nilai
        $submission->update([
            'status_revisi' => $request->status_revisi,
            'nilai' => $request->nilai,
        ]);

        // 4. (PENTING) Simpan Komentar ke Tabel 'komentars' jika dosen mengisi
        if ($request->filled('komentar')) {
            \App\Models\Komentar::create([
                'submission_id' => $submission->id,
                'user_id' => auth()->id(), // ID Dosen yang sedang login
                'komentar' => $request->komentar,
                // 'file_lampiran' => ... (jika ada fitur upload file balasan)
            ]);
            
            // Opsional: Buat notifikasi ke mahasiswa bahwa ada komentar baru
        }

        return back()->with('success', 'Penilaian berhasil disimpan!');
    }

    // Download submission file
    public function download($id)
    {
        $submission = Submission::findOrFail($id);
        
        // Authorization check
        $user = Auth::user();
        if ($user->isMahasiswa() && $submission->mahasiswa_id !== $user->id) {
            abort(403);
        }
        
        if ($user->isDosen() && $submission->tugas->kelas->dosen_id !== $user->id) {
            abort(403);
        }
        
        return Storage::disk('public')->download($submission->file_submission);
    }

    // Mahasiswa - Delete submission (only if pending)
    public function destroy($id)
    {
        if (!Auth::user()->isMahasiswa()) {
            abort(403);
        }
        
        $submission = Submission::findOrFail($id);
        
        if ($submission->mahasiswa_id !== Auth::id()) {
            abort(403);
        }
        
        if ($submission->status_revisi !== 'pending') {
            return back()->with('error', 'Tidak dapat menghapus submission yang sudah dinilai!');
        }
        
        Storage::disk('public')->delete($submission->file_submission);
        $submission->delete();
        
        return back()->with('success', 'Submission berhasil dihapus!');
    }

    // Notify dosen about new submission
    private function notifyDosen($submission)
    {
        $dosen = $submission->tugas->kelas->dosen;
        
        Notification::create([
            'user_id' => $dosen->id,
            'title' => 'Submission Baru',
            'message' => $submission->mahasiswa->name . ' telah mengumpulkan tugas "' . $submission->tugas->judul . '"',
            'type' => 'submission',
            'link' => route('tugas.show', $submission->tugas_id),
        ]);
    }

    // Notify mahasiswa about status update
    private function notifyMahasiswa($submission)
    {
        $statusText = [
            'diterima' => 'diterima',
            'revisi' => 'perlu direvisi',
            'ditolak' => 'ditolak'
        ];
        
        Notification::create([
            'user_id' => $submission->mahasiswa_id,
            'title' => 'Update Status Tugas',
            'message' => 'Tugas "' . $submission->tugas->judul . '" telah ' . $statusText[$submission->status_revisi] . ($submission->nilai ? ' dengan nilai ' . $submission->nilai : ''),
            'type' => 'grade',
            'link' => route('tugas.show', $submission->tugas_id),
        ]);
    }

    // Menampilkan Halaman Detail Penilaian (Pengganti Modal)
    public function showGradingPage($id)
    {
        // Ambil data submission beserta relasi yang dibutuhkan (komentar, user, tugas, kelas)
        $submission = Submission::with(['tugas.kelas.mataKuliah', 'mahasiswa', 'komentar.user'])
            ->findOrFail($id);

        // Pastikan yang akses adalah Dosen
        if (!auth()->user()->isDosen()) {
            abort(403, 'Akses ditolak.');
        }

        return view('dosen.submissions.grade', compact('submission'));
    }
}