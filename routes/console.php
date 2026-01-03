<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Contoh bawaan Laravel (boleh dihapus jika tidak pakai)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// --- KODE JADWAL ANDA MULAI DI SINI ---

// Send deadline reminders every day at 8 AM
Schedule::command('reminders:deadline')->dailyAt('08:00');

// Clean old notifications every week
Schedule::call(function () {
    (new \App\Services\NotificationService())->clearOldNotifications();
})->weekly();