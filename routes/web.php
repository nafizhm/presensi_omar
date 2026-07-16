<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\LocationPointController;
use App\Http\Controllers\Admin\AttendanceLogController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\EmployeeScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(
        auth()->user()->role === 'admin' ? 'admin.dashboard' : 'presensi.beranda'
    );
});

Route::get('/dashboard', function () {
    return redirect()->route('presensi.beranda');
})->middleware(['auth', 'verified', 'role:karyawan'])->name('dashboard');

Route::middleware(['auth', 'role:karyawan'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:karyawan'])->prefix('presensi')->name('presensi.')->group(function () {
    Route::get('/', [PresensiController::class, 'beranda'])->name('beranda');
    Route::get('/checkin', [PresensiController::class, 'checkinForm'])->name('checkin');
    Route::post('/checkin', [PresensiController::class, 'store'])->name('store');
    Route::post('/checkout', [PresensiController::class, 'checkout'])->name('checkout');
    Route::get('/riwayat', [PresensiController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [PresensiController::class, 'profil'])->name('profil');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/presensi/log', [AttendanceLogController::class, 'index'])
            ->name('attendance.logs.index');
        Route::resource('/presensi/setting', ShiftController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('attendance.settings')
            ->parameters(['setting' => 'shift']);
        Route::get('/presensi/set-jadwal', [EmployeeScheduleController::class, 'index'])
            ->name('attendance.employee-schedules.index');
        Route::get('/presensi/set-jadwal/{employee}', [EmployeeScheduleController::class, 'edit'])
            ->name('attendance.employee-schedules.edit');
        Route::put('/presensi/set-jadwal/{employee}', [EmployeeScheduleController::class, 'update'])
            ->name('attendance.employee-schedules.update');
        Route::resource('karyawan', EmployeeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['karyawan' => 'employee']);
        Route::get('/pengaturan/profile', [CompanyProfileController::class, 'edit'])
            ->name('settings.profile.edit');
        Route::put('/pengaturan/profile', [CompanyProfileController::class, 'update'])
            ->name('settings.profile.update');
        Route::resource('/pengaturan/pengguna', AdminUserController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('settings.users')
            ->parameters(['pengguna' => 'adminUser']);
        Route::resource('/pengaturan/titik-lokasi', LocationPointController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('settings.locations')
            ->parameters(['titik-lokasi' => 'location']);
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    });
});

require __DIR__.'/auth.php';
