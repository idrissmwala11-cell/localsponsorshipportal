<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AccountApprovalController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSetupController;
use App\Http\Controllers\Admin\CenterReportController;
use App\Http\Controllers\Admin\OfficialAdminController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CenterNotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParticipantTreatmentController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ParticipantSponsorshipController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/media/public/{path}', [PublicMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.public');

Route::middleware('auth')->group(function () {
    Route::get('/account/pending-approval', [AccountApprovalController::class, 'pending'])->name('approval.pending');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/otp/verify', [OtpController::class, 'showVerifyForm'])->name('otp.verify');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify.submit');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');
});

Route::middleware(['auth', 'otp', 'approved', 'admin_setup'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/church-profile', [DashboardController::class, 'updateChurchProfile'])->name('dashboard.church-profile.update');
    Route::delete('/dashboard/church-profile/photos/{photoIndex}', [DashboardController::class, 'deleteChurchPhoto'])->name('dashboard.church-profile.photos.delete');
    Route::get('/attendance/program', [AttendanceController::class, 'program'])->name('attendance.program.index');
    Route::post('/attendance/program', [AttendanceController::class, 'storeProgram'])->name('attendance.program.store');
    Route::get('/attendance/activity', [AttendanceController::class, 'activity'])->name('attendance.activity.index');
    Route::post('/attendance/activity', [AttendanceController::class, 'storeActivity'])->name('attendance.activity.store');
    Route::get('/treatments', [ParticipantTreatmentController::class, 'index'])->name('treatments.index');
    Route::post('/treatments', [ParticipantTreatmentController::class, 'store'])->name('treatments.store');
    Route::get('/reports', [CenterReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [CenterReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/export/{type}', [CenterReportController::class, 'export'])->name('reports.export');

    Route::get('/participants/search', [ParticipantController::class, 'search'])->name('participants.search');
    Route::resource('participants', ParticipantController::class);

    // Standalone Sponsorship Module
    Route::resource('sponsorships', ParticipantSponsorshipController::class)->except(['show']);

    Route::get('/notifications', [CenterNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [CenterNotificationController::class, 'send'])->name('notifications.send');
    Route::post('/notifications/mark-all-read', [CenterNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [CenterNotificationController::class, 'markRead'])->name('notifications.read');
});

Route::middleware(['auth', 'otp', 'approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/setup', [AdminSetupController::class, 'show'])->name('setup.show');
    Route::post('/setup', [AdminSetupController::class, 'store'])->name('setup.store');

    Route::middleware('admin_setup')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');

        Route::middleware('official_admin')->group(function () {
            Route::get('/official', [OfficialAdminController::class, 'index'])->name('official.index');
        });

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/participants/{participant}/export', [UserManagementController::class, 'exportParticipant'])->name('users.participants.export');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    });
});

require __DIR__ . '/auth.php';
