<?php

use App\Http\Controllers\PresensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('presensi')->name('presensi.')->group(function () {
    Route::get('/', [PresensiController::class, 'beranda'])->name('beranda');
    Route::get('/checkin', [PresensiController::class, 'checkinForm'])->name('checkin');
    Route::post('/checkin', [PresensiController::class, 'store'])->name('store');
    Route::post('/checkout', [PresensiController::class, 'checkout'])->name('checkout');
    Route::get('/riwayat', [PresensiController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [PresensiController::class, 'profil'])->name('profil');
});
