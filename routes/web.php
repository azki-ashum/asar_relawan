<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\BlockedUserController;
use App\Http\Controllers\RelawanDashboardController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\Admin\RelawanController;
use App\Http\Controllers\Admin\BidangRelawanController;
use App\Http\Controllers\Admin\PengajuanController as AdminPengajuanController;
use Illuminate\Support\Facades\Artisan;

Route::get('/clear-caches', function () {
  Artisan::call('cache:clear');
  Artisan::call('route:clear');
  Artisan::call('config:clear');
  Artisan::call('view:clear');
  return "All caches cleared!";
});

// Fallback: redirect any undefined route to the homepage
Route::fallback(function () {
    return redirect('/');
});

Route::get('/', function () {
    return view('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

// Route untuk Google Login
Route::post('/login/google', [GoogleLoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/google/callback', [GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

// Logout route
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Sistem Pengajuan Relawan
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ---- Pengaju ----
    Route::get('/relawan/dashboard', [RelawanDashboardController::class, 'index'])->name('relawan.dashboard');

    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/create', [PengajuanController::class, 'create'])->name('pengajuan.create');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
    Route::get('/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('/pengajuan/{pengajuan}/edit', [PengajuanController::class, 'edit'])->name('pengajuan.edit');
    Route::put('/pengajuan/{pengajuan}', [PengajuanController::class, 'update'])->name('pengajuan.update');
    Route::delete('/pengajuan/{pengajuan}', [PengajuanController::class, 'destroy'])->name('pengajuan.destroy');
    Route::post('/pengajuan/{pengajuan}/selesai', [PengajuanController::class, 'selesai'])->name('pengajuan.selesai');

    // ---- Admin: Data Relawan (SDM) ----
    Route::get('/admin/relawan', [RelawanController::class, 'index'])->name('admin.relawan.index');
    Route::get('/admin/relawan/create', [RelawanController::class, 'create'])->name('admin.relawan.create');
    Route::post('/admin/relawan', [RelawanController::class, 'store'])->name('admin.relawan.store');
    Route::get('/admin/relawan/{relawan}/edit', [RelawanController::class, 'edit'])->name('admin.relawan.edit');
    Route::put('/admin/relawan/{relawan}', [RelawanController::class, 'update'])->name('admin.relawan.update');
    Route::delete('/admin/relawan/{relawan}', [RelawanController::class, 'destroy'])->name('admin.relawan.destroy');

    // ---- Admin: Bidang Relawan ----
    Route::post('/admin/bidang-relawan', [BidangRelawanController::class, 'store'])->name('admin.bidang_relawan.store');
    Route::put('/admin/bidang-relawan/{bidang}', [BidangRelawanController::class, 'update'])->name('admin.bidang_relawan.update');
    Route::delete('/admin/bidang-relawan/{bidang}', [BidangRelawanController::class, 'destroy'])->name('admin.bidang_relawan.destroy');

    // ---- Admin: Pengajuan (verifikasi & penugasan sesuai SOP) ----
    Route::get('/admin/pengajuan', [AdminPengajuanController::class, 'index'])->name('admin.pengajuan.index');
    Route::get('/admin/pengajuan/{pengajuan}', [AdminPengajuanController::class, 'show'])->name('admin.pengajuan.show');
    Route::delete('/admin/pengajuan/{pengajuan}', [AdminPengajuanController::class, 'destroy'])->name('admin.pengajuan.destroy');
    // Bagian 1: Review & Verifikasi
    Route::post('/admin/pengajuan/{pengajuan}/approve', [AdminPengajuanController::class, 'approve'])->name('admin.pengajuan.approve');
    Route::post('/admin/pengajuan/{pengajuan}/revisi', [AdminPengajuanController::class, 'revisi'])->name('admin.pengajuan.revisi');
    Route::post('/admin/pengajuan/{pengajuan}/reject', [AdminPengajuanController::class, 'reject'])->name('admin.pengajuan.reject');
    // Bagian 2: Penugasan per baris kebutuhan
    Route::get('/admin/pengajuan/{pengajuan}/assign', [AdminPengajuanController::class, 'assignForm'])->name('admin.pengajuan.assign_form');
    Route::post('/admin/pengajuan/{pengajuan}/kebutuhan/{kebutuhan}/assign', [AdminPengajuanController::class, 'assignKebutuhan'])->name('admin.pengajuan.kebutuhan.assign');
    Route::post('/admin/pengajuan/{pengajuan}/kebutuhan/{kebutuhan}/unassign', [AdminPengajuanController::class, 'unassignKebutuhan'])->name('admin.pengajuan.kebutuhan.unassign');
    Route::post('/admin/pengajuan/{pengajuan}/tugaskan', [AdminPengajuanController::class, 'tugaskan'])->name('admin.pengajuan.tugaskan');
    // Bagian 3: revisi laporan
    Route::post('/admin/pengajuan/{pengajuan}/revisi-laporan', [AdminPengajuanController::class, 'revisiLaporan'])->name('admin.pengajuan.revisi_laporan');

    // ---- Admin: manajemen pengguna & blokir email (generik, bukan khusus booking) ----
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/blocked-users', [BlockedUserController::class, 'index'])->name('admin.blocked_users.index');
    Route::post('/admin/blocked-users', [BlockedUserController::class, 'store'])->name('admin.blocked_users.store');
    Route::delete('/admin/blocked-users/{blockedUser}', [BlockedUserController::class, 'destroy'])->name('admin.blocked_users.destroy');
    Route::get('/admin/blocked-users/search', [BlockedUserController::class, 'search'])->name('admin.blocked_users.search');
});
