<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Tugas;

class NotificationService
{
    /**
     * Send deadline reminder notifications
     */
    public function sendDeadlineReminders()
    {
        // Get tugas with deadline in next 24 hours
        $upcomingTugas = Tugas::where('status', 'published')
            ->whereBetween('deadline', [now(), now()->addDay()])
            ->with(['kelas.enrollments.mahasiswa'])
            ->get();
        
        foreach ($upcomingTugas as $tugas) {
            foreach ($tugas->kelas->enrollments as $enrollment) {
                $mahasiswa = $enrollment->mahasiswa;
                
                // Check if already submitted
                $submitted = $tugas->submissions()
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('status_revisi', '!=', 'ditolak')
                    ->exists();
                
                if (!$submitted) {
                    Notification::create([
                        'user_id' => $mahasiswa->id,
                        'title' => '⚠️ Deadline Mendekat!',
                        'message' => 'Tugas "' . $tugas->judul . '" akan berakhir dalam 24 jam. Segera kumpulkan!',
                        'type' => 'deadline',
                        'link' => route('tugas.show', $tugas->id),
                    ]);
                }
            }
        }
    }

    /**
     * Send notification for new tugas
     */
    public function notifyNewTugas(Tugas $tugas)
    {
        $mahasiswaIds = $tugas->kelas->enrollments()->pluck('mahasiswa_id');
        
        foreach ($mahasiswaIds as $mahasiswaId) {
            Notification::create([
                'user_id' => $mahasiswaId,
                'title' => '📝 Tugas Baru',
                'message' => 'Tugas baru "' . $tugas->judul . '" telah ditambahkan untuk ' . $tugas->kelas->mataKuliah->nama_mk,
                'type' => 'deadline',
                'link' => route('tugas.show', $tugas->id),
            ]);
        }
    }

    /**
     * Send notification for new submission
     */
    public function notifyNewSubmission($submission)
    {
        $dosen = $submission->tugas->kelas->dosen;
        
        Notification::create([
            'user_id' => $dosen->id,
            'title' => '📄 Submission Baru',
            'message' => $submission->mahasiswa->name . ' telah mengumpulkan tugas "' . $submission->tugas->judul . '"',
            'type' => 'submission',
            'link' => route('tugas.show', $submission->tugas_id),
        ]);
    }

    /**
     * Send notification for grade update
     */
    public function notifyGradeUpdate($submission)
    {
        $statusText = [
            'diterima' => 'diterima',
            'revisi' => 'perlu direvisi',
            'ditolak' => 'ditolak'
        ];
        
        $message = 'Tugas "' . $submission->tugas->judul . '" telah ' . $statusText[$submission->status_revisi];
        
        if ($submission->nilai) {
            $message .= ' dengan nilai ' . $submission->nilai;
        }
        
        Notification::create([
            'user_id' => $submission->mahasiswa_id,
            'title' => '✅ Update Nilai',
            'message' => $message,
            'type' => 'grade',
            'link' => route('tugas.show', $submission->tugas_id),
        ]);
    }

    /**
     * Send notification for new comment
     */
    public function notifyNewComment($komentar, $submission)
    {
        $sender = $komentar->user;
        
        if ($sender->isDosen()) {
            // Notify mahasiswa
            $recipientId = $submission->mahasiswa_id;
            $title = '💬 Komentar dari Dosen';
        } else {
            // Notify dosen
            $recipientId = $submission->tugas->kelas->dosen_id;
            $title = '💬 Komentar dari Mahasiswa';
        }
        
        Notification::create([
            'user_id' => $recipientId,
            'title' => $title,
            'message' => $sender->name . ' memberikan komentar pada tugas "' . $submission->tugas->judul . '"',
            'type' => 'comment',
            'link' => route('tugas.show', $submission->tugas_id),
        ]);
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(array $userIds, string $title, string $message, string $type = 'announcement', ?string $link = null)
    {
        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
            ]);
        }
    }

    /**
     * Clear old notifications (older than 30 days)
     */
    public function clearOldNotifications()
    {
        Notification::where('created_at', '<', now()->subDays(30))
            ->where('is_read', true)
            ->delete();
    }
}