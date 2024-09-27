<?php
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudyGuideController;
use App\Http\Middleware\FetchSchool;
use App\Http\Middleware\CheckUserActivation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Algemene routes voor het dashboard, werkstukken en flitskaarten (geen CheckUserMembership nodig)
Route::middleware(['auth', 'verified', FetchSchool::class, CheckUserActivation::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/leerlingen', [StudentController::class, 'index'])->name('dashboard.student.index');
    Route::get('/leerling/bekijken/{id}', [StudentController::class, 'view'])->name('dashboard.student.view');

    Route::get('/cijfers', [GradeController::class, 'index'])->name('dashboard.homework.index');
    Route::get('/cijfer/toevoegen', [GradeController::class, 'create'])->name('dashboard.homework.view');
    Route::get('/cijfer/bekijken/{id}', [GradeController::class, 'view'])->name('dashboard.homework.view');
    Route::get('/cijfer/bewerken/{id}', [GradeController::class, 'edit'])->name('dashboard.homework.view');

    Route::get('/notities', [NoteController::class, 'index'])->name('dashboard.homework.index');
    Route::get('/notities/toevoegen', [NoteController::class, 'create'])->name('dashboard.homework.view');
    Route::get('/notities/bekijken/{id}', [NoteController::class, 'view'])->name('dashboard.homework.view');
    Route::get('/notities/bewerken/{id}', [NoteController::class, 'edit'])->name('dashboard.homework.view');

    Route::get('/studiewijzers', [StudyGuideController::class, 'index'])->name('dashboard.studyguide.index');
    Route::get('/studiewijzer/bekijken/{id}', [StudyGuideController::class, 'view'])->name('dashboard.studyguide.view');
    Route::get('/studiewijzer/bewerken/{id}', [StudyGuideController::class, 'edit'])->name('dashboard.studyguide.edit');
    Route::delete('/studiewijzer/verwijderen/{id}', [StudyGuideController::class, 'verwijder'])->name('dashboard.studyguide.verwijder');
    Route::get('/studiewijzer/toevoegen', [StudyGuideController::class, 'create'])->name('dashboard.studyguide.create');
    Route::get('/studiewijzer/{id}/huiswerk/aanmaken', [StudyGuideController::class, 'createHomework'])->name('dashboard.studyguide.create_homework');
    Route::post('/studiewijzer/add', [StudyGuideController::class, 'store'])->name('dashboard.studyguide.store');
    Route::delete('studiewijzer/huiswerk/verwijderen/{id}', [StudyGuideController::class, 'destroy'])->name('dashboard.studyguide.destroy_homework');
    Route::post('/huiswerk/add', [StudyGuideController::class, 'storeHomework'])->name('dashboard.studyguide.store_homework');
    Route::get('get-classes-by-subject', [StudyGuideController::class, 'getClassesBySubject'])->name('get.classes.by.subject');
    Route::get('get-students-by-class', [StudyGuideController::class, 'getStudentsByClass'])->name('get.students.by.class');

    Route::get('/school', [SchoolController::class, 'index'])->name('school.index');
});


// Account gerelateerde routes (toegankelijk voor iedereen)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('account')->group(function () {
        Route::get('/profiel', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/instellingen', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::get('/beveiliging', [ProfileController::class, 'security'])->name('profile.security');
    });
    Route::post('/account/update-bio', [ProfileController::class, 'updateBio'])->name('profile.updateBio');
    Route::post('/account/update-security', [ProfileController::class, 'updateSecurity'])->name('profile.updateSecurity');
});

// Auth routes (geen middleware nodig)
Route::get('/inloggen', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/inloggen', [AuthController::class, 'login'])->name('login.post');
Route::get('/wachtwoord/vergeten', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/wachtwoord/vergeten', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/wachtwoord/reset/{token?}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/wachtwoord/reset', [AuthController::class, 'resetPassword'])->name('password.update');

// Uitlog route
Route::middleware(['auth'])->post('/api/uitloggen', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/inloggen');
})->name('logout');

// E-mail verificatie routes
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});
