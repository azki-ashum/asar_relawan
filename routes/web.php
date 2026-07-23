<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\AssetDashboardController;
use App\Http\Controllers\AssetBookingController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\BlockedUserController;
use App\Http\Controllers\RelawanDashboardController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\Admin\RelawanController;
use App\Http\Controllers\Admin\BidangRelawanController;
use App\Http\Controllers\Admin\PengajuanController as AdminPengajuanController;
use App\Models\Asset;
use App\Models\AssetType;
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

// Dashboard (protected)
Route::get('/room/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// After login, show a choice page so user can pick Room or Asset dashboard
Route::get('/choose', function () {
    return view('choose');
})->middleware('auth')->name('choose');

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

// Booking & Room routes
Route::middleware('auth')->group(function () {
    
    // ---- Asset Route -----

    // Asset dashboard (dummy controller/view for new asset flow)
    Route::get('/asset/dashboard', [AssetDashboardController::class, 'index'])->name('dashboard.asset');
    // Convenience route: allow /asset as the asset dashboard root for compatibility with navbar
    Route::get('/asset', [AssetDashboardController::class, 'index'])->name('asset.home');

    // Asset bookings (controller)
    Route::get('/asset/bookings', [AssetBookingController::class, 'index'])->name('asset.bookings.index');
    Route::get('/asset/bookings/create', [AssetBookingController::class, 'create'])->name('asset.bookings.create');
    Route::post('/asset/bookings', [AssetBookingController::class, 'store'])->name('asset.bookings.store');
    Route::get('/asset/bookings/{booking}/edit', [AssetBookingController::class, 'edit'])->name('asset.bookings.edit');
    Route::put('/asset/bookings/{booking}', [AssetBookingController::class, 'update'])->name('asset.bookings.update');
    Route::post('/asset/bookings/{booking}/cancel', [AssetBookingController::class, 'cancel'])->name('asset.bookings.cancel');
    Route::post('/asset/bookings/{booking}/complete', [AssetBookingController::class, 'complete'])->name('asset.bookings.complete');
    Route::post('/asset/bookings/{booking}/resubmit', [AssetBookingController::class, 'resubmit'])->name('asset.bookings.resubmit');

    // Admin asset management (controller-based, mirror room admin flow)
    Route::get('/asset/admin/assets', [AssetController::class, 'adminIndex'])->name('admin.assets.index');
    Route::get('/asset/admin/assets/create', [AssetController::class, 'create'])->name('admin.assets.create');
    Route::post('/asset/admin/assets', [AssetController::class, 'store'])->name('admin.assets.store');
    Route::get('/asset/admin/assets/{asset}/edit', [AssetController::class, 'edit'])->name('admin.assets.edit');
    Route::put('/asset/admin/assets/{asset}', [AssetController::class, 'update'])->name('admin.assets.update');
    Route::delete('/asset/admin/assets/{asset}', [AssetController::class, 'destroy'])->name('admin.assets.destroy');

    // Asset Type admin routes (follow admin naming)
    Route::get('/asset/admin/types/create', [AssetTypeController::class, 'create'])->name('admin.asset_types.create');
    Route::post('/asset/admin/types', [AssetTypeController::class, 'store'])->name('admin.asset_types.store');
    Route::get('/asset/admin/types/{type}/edit', [AssetTypeController::class, 'edit'])->name('admin.asset_types.edit');
    Route::put('/asset/admin/types/{type}', [AssetTypeController::class, 'update'])->name('admin.asset_types.update');
    Route::delete('/asset/admin/types/{type}', [AssetTypeController::class, 'destroy'])->name('admin.asset_types.destroy');

    Route::get('/asset/admin/bookings', [AssetBookingController::class, 'adminIndex'])->name('asset.admin.bookings.index');
    // admin edit/update for asset bookings
    Route::get('/asset/admin/bookings/{booking}/edit', [AssetBookingController::class, 'adminEdit'])->name('asset.admin.bookings.edit');
    Route::put('/asset/admin/bookings/{booking}', [AssetBookingController::class, 'adminUpdate'])->name('asset.admin.bookings.update');
    Route::delete('/asset/admin/bookings/{booking}', [AssetBookingController::class, 'adminDestroy'])->name('asset.admin.bookings.destroy');
    Route::post('/asset/admin/bookings/{booking}/revision', [AssetBookingController::class, 'adminRevision'])->name('asset.admin.bookings.revision');
    Route::post('/asset/admin/bookings/{booking}/cancel-revision', [AssetBookingController::class, 'cancelRevision'])->name('asset.admin.bookings.cancel_revision');
    // (checkout route removed — status will be updated automatically by scheduler at start_at)
    
    
    // ---- Room (Ruangan) Route -----
    
    // rooms
    Route::get('/room/rooms', [RoomController::class, 'index'])->name('rooms.index');

    // bookings
    Route::get('/room/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/room/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/room/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/room/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/room/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::post('/room/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/room/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');

    // Calendar data endpoints (also exposed under /api/* for the dashboard JS)
    Route::get('/room/api/bookings/calendar-dates', [BookingController::class, 'calendarDates']);
    Route::get('/room/api/bookings/by-date', [BookingController::class, 'bookingsByDate']);

    // Asset calendar API for dashboard widgets
    Route::get('/asset/api/bookings/calendar-dates', [AssetBookingController::class, 'calendarDates']);
    Route::get('/asset/api/bookings/by-date', [AssetBookingController::class, 'bookingsByDate']);

    // serve snapshot images safely
    Route::get('/asset/bookings/{booking}/snapshot', [AssetBookingController::class, 'snapshot'])->name('asset.bookings.snapshot');

    // admin bookings
    Route::get('/room/admin/bookings', [BookingController::class, 'adminIndex'])->name('admin.bookings.index');
    Route::post('/room/admin/bookings/{booking}/override', [BookingController::class, 'adminOverride'])->name('admin.bookings.override');
    Route::get('/room/admin/bookings/{booking}/edit', [BookingController::class, 'adminEdit'])->name('admin.bookings.edit');
    Route::put('/room/admin/bookings/{booking}', [BookingController::class, 'adminUpdate'])->name('admin.bookings.update');
    Route::delete('/room/admin/bookings/{booking}', [BookingController::class, 'adminDestroy'])->name('admin.bookings.destroy');

    // admin rooms
    Route::get('/room/admin/rooms', [RoomController::class, 'adminIndex'])->name('admin.rooms.index');
    Route::get('/room/admin/rooms/create', [RoomController::class, 'create'])->name('admin.rooms.create');
    Route::post('/room/admin/rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::get('/room/admin/rooms/{room}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::put('/room/admin/rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('/room/admin/rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // admin blocked users (email based blocking)
    Route::get('/admin/blocked-users', [BlockedUserController::class, 'index'])->name('admin.blocked_users.index');
    Route::post('/admin/blocked-users', [BlockedUserController::class, 'store'])->name('admin.blocked_users.store');
    Route::delete('/admin/blocked-users/{blockedUser}', [BlockedUserController::class, 'destroy'])->name('admin.blocked_users.destroy');
    Route::get('/admin/blocked-users/search', [BlockedUserController::class, 'search'])->name('admin.blocked_users.search');

    // Admin user management
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

    // Admin hub (landing page for all admin menus)
    Route::get('/admin/hub', [AdminDashboardController::class, 'hub'])->name('admin.hub');

    // Admin statistics dashboard (rooms & assets)
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

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
    Route::post('/pengajuan/{pengajuan}/resubmit', [PengajuanController::class, 'resubmit'])->name('pengajuan.resubmit');

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

    // ---- Admin: Pengajuan (cari & assign relawan) ----
    Route::get('/admin/pengajuan', [AdminPengajuanController::class, 'index'])->name('admin.pengajuan.index');
    Route::get('/admin/pengajuan/{pengajuan}', [AdminPengajuanController::class, 'show'])->name('admin.pengajuan.show');
    Route::get('/admin/pengajuan/{pengajuan}/assign', [AdminPengajuanController::class, 'assignForm'])->name('admin.pengajuan.assign_form');
    Route::post('/admin/pengajuan/{pengajuan}/assign', [AdminPengajuanController::class, 'assign'])->name('admin.pengajuan.assign');
    Route::post('/admin/pengajuan/{pengajuan}/revisi', [AdminPengajuanController::class, 'revisi'])->name('admin.pengajuan.revisi');
    Route::post('/admin/pengajuan/{pengajuan}/cancel-revision', [AdminPengajuanController::class, 'cancelRevision'])->name('admin.pengajuan.cancel_revision');
    Route::post('/admin/pengajuan/{pengajuan}/reject', [AdminPengajuanController::class, 'reject'])->name('admin.pengajuan.reject');
});
