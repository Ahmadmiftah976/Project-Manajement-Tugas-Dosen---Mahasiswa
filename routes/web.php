<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\ProfileController; // Pastikan controller ini ada/di-import
use App\Http\Controllers\AdminController;   // Pastikan controller ini ada/di-import

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================================================================
    // PERBAIKAN: Routing Kelas Management (Dosen) DIPINDAHKAN KE SINI (ATAS)
    // Agar 'create', 'edit', dll dibaca lebih dulu sebelum '/{id}'
    // =========================================================================
    Route::prefix('kelas')->middleware('role:dosen')->group(function () {
        Route::get('/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::post('/', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/{id}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');
        Route::get('/{id}/enrollments', [KelasController::class, 'manageEnrollments'])->name('kelas.enrollments');
        Route::post('/{id}/mahasiswa', [KelasController::class, 'addMahasiswa'])->name('kelas.add-mahasiswa');
        Route::delete('/{kelasId}/mahasiswa/{mahasiswaId}', [KelasController::class, 'removeMahasiswa'])->name('kelas.remove-mahasiswa');
    });

    // =========================================================================
    // Routing Kelas Umum (Read/View) - DILETAKKAN SETELAH ROUTE KHUSUS DI ATAS
    // =========================================================================
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::get('/kelas/{id}', [KelasController::class, 'show'])->name('kelas.show');
    
    // Tugas Routes
    Route::prefix('kelas/{kelasId}/tugas')->group(function () {
        Route::get('/', [TugasController::class, 'index'])->name('tugas.index');
        Route::get('/create', [TugasController::class, 'create'])->name('tugas.create')->middleware('role:dosen');
        Route::post('/', [TugasController::class, 'store'])->name('tugas.store')->middleware('role:dosen');
    });
    
    Route::prefix('tugas')->group(function () {
        Route::get('/{id}', [TugasController::class, 'show'])->name('tugas.show');
        Route::get('/{id}/edit', [TugasController::class, 'edit'])->name('tugas.edit')->middleware('role:dosen');
        Route::put('/{id}', [TugasController::class, 'update'])->name('tugas.update')->middleware('role:dosen');
        Route::delete('/{id}', [TugasController::class, 'destroy'])->name('tugas.destroy')->middleware('role:dosen');
    });
    
    // Submission Routes
    Route::prefix('submissions')->group(function () {
        Route::post('/tugas/{tugasId}', [SubmissionController::class, 'store'])->name('submissions.store')->middleware('role:mahasiswa');
        Route::put('/{id}/status', [SubmissionController::class, 'updateStatus'])->name('submissions.updateStatus')->middleware('role:dosen');
        Route::get('/{id}/download', [SubmissionController::class, 'download'])->name('submissions.download');
        Route::delete('/{id}', [SubmissionController::class, 'destroy'])->name('submissions.destroy')->middleware('role:mahasiswa');
    });
    
    // Komentar Routes
    Route::post('/komentar', [KomentarController::class, 'store'])->name('komentar.store');
    Route::delete('/komentar/{id}', [KomentarController::class, 'destroy'])->name('komentar.destroy');
    
    // Notification Routes
    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.deleteAvatar');
    Route::get('/profile/user/{id}', [ProfileController::class, 'showUser'])->name('profile.user');
    
    // Mahasiswa Routes
    Route::prefix('mahasiswa')->name('mahasiswa.')->middleware('role:mahasiswa')->group(function () {
        Route::get('/classes', [MahasiswaController::class, 'myClasses'])->name('classes');
        Route::get('/tugas', [MahasiswaController::class, 'myTugas'])->name('tugas');
        Route::get('/submissions', [MahasiswaController::class, 'mySubmissions'])->name('submissions');
        Route::get('/grades', [MahasiswaController::class, 'myGrades'])->name('grades');
        Route::get('/calendar', [MahasiswaController::class, 'calendar'])->name('calendar');
        Route::get('/statistics', [MahasiswaController::class, 'statistics'])->name('statistics');
        Route::get('/export/transcript', [MahasiswaController::class, 'exportTranscript'])->name('export.transcript');
    });
    
    // Dosen Routes
    Route::prefix('dosen')->name('dosen.')->middleware('role:dosen')->group(function () {
        Route::get('/classes', [DosenController::class, 'myClasses'])->name('classes');
        Route::get('/tugas', [DosenController::class, 'myTugas'])->name('tugas');
        Route::get('/submissions', [DosenController::class, 'submissions'])->name('submissions');
        Route::get('/tugas/{id}/batch-grading', [DosenController::class, 'batchGrading'])->name('batch-grading');
        Route::post('/tugas/{id}/batch-grades', [DosenController::class, 'storeBatchGrades'])->name('store-batch-grades');
        Route::get('/kelas/{id}/mahasiswa', [DosenController::class, 'mahasiswaList'])->name('mahasiswa.list');
        Route::get('/kelas/{kelasId}/mahasiswa/{mahasiswaId}', [DosenController::class, 'mahasiswaDetail'])->name('mahasiswa.detail');
        Route::get('/statistics', [DosenController::class, 'statistics'])->name('statistics');
        Route::get('/kelas/{id}/export', [DosenController::class, 'exportReport'])->name('export.report');
    });
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        
        // Mata Kuliah Management
        Route::get('/matakuliah', [AdminController::class, 'mataKuliah'])->name('matakuliah.index');
        Route::get('/matakuliah/create', [AdminController::class, 'createMataKuliah'])->name('matakuliah.create');
        Route::post('/matakuliah', [AdminController::class, 'storeMataKuliah'])->name('matakuliah.store');
        Route::get('/matakuliah/{id}/edit', [AdminController::class, 'editMataKuliah'])->name('matakuliah.edit');
        Route::put('/matakuliah/{id}', [AdminController::class, 'updateMataKuliah'])->name('matakuliah.update');
        Route::delete('/matakuliah/{id}', [AdminController::class, 'deleteMataKuliah'])->name('matakuliah.delete');
        
        // Kelas Management
        Route::get('/kelas', [AdminController::class, 'kelas'])->name('kelas.index');
        
        // Reports
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        
        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });

        // Route khusus Mahasiswa
    Route::middleware(['auth'])->group(function () {
        // Halaman Mata Kuliah Saya
        Route::get('/mahasiswa/kelas', [App\Http\Controllers\KelasController::class, 'myClasses'])
            ->name('mahasiswa.kelas.index');
            
        // Halaman Tugas Saya
        Route::get('/mahasiswa/tugas', [App\Http\Controllers\TugasController::class, 'myTasks'])
            ->name('mahasiswa.tugas.index');

        // Route khusus untuk Dosen membuka halaman penilaian detail
        Route::get('/submissions/{id}/grade', [App\Http\Controllers\SubmissionController::class, 'showGradingPage'])
            ->name('submissions.grade');    
    });
});