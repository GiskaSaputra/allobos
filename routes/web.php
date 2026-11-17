<?php

use App\Http\Controllers\ActiveRoleController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Bos;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pengelola;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public;
use App\Http\Controllers\Warga;
use Illuminate\Support\Facades\Route;

Route::get('/', [Public\PageController::class, 'home'])->name('home');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('peran-aktif', [ActiveRoleController::class, 'update'])->name('active-role.update');
    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifikasi', [NotificationController::class, 'read'])->name('notifications.read');

    Route::middleware('role:warga')->prefix('warga')->name('warga.')->group(function () {
        Route::get('kenali/{step?}', [Warga\OnboardingController::class, 'show'])->whereNumber('step')->name('onboarding.show');
        Route::post('kenali/{step}', [Warga\OnboardingController::class, 'store'])->whereNumber('step')->name('onboarding.store');

        Route::middleware('onboarded')->group(function () {
            Route::get('/', Warga\DashboardController::class)->name('dashboard');
            Route::get('jejak', [Warga\DashboardController::class, 'journey'])->name('journey');
        });
    });

    Route::middleware(['role:warga', 'onboarded'])->prefix('ruang-bos')->name('boss-room.')->controller(Warga\BossRoomController::class)->group(function () {
        Route::get('/', 'setup')->name('setup');
        Route::post('/', 'store')->name('store');
        Route::get('riwayat', 'history')->name('history');
        Route::get('{session}', 'show')->whereNumber('session')->name('show');
        Route::post('{session}/mulai', 'start')->whereNumber('session')->name('start');
        Route::post('{session}/kirim', 'send')->whereNumber('session')->middleware('throttle:ai')->name('send');
        Route::post('{session}/akhiri', 'finish')->whereNumber('session')->name('finish');
        Route::get('{session}/catatan', 'note')->whereNumber('session')->name('note');
        Route::get('{session}/catatan/pdf', 'pdf')->whereNumber('session')->name('note.pdf');
        Route::post('{session}/langkah', 'addSteps')->whereNumber('session')->name('steps');
    });

    Route::middleware('role:bos')->prefix('bos-area')->name('bos.')->group(function () {
        Route::get('/', Bos\DashboardController::class)->name('dashboard');
    });

    Route::middleware('role:pengelola,admin')->prefix('pengelola')->name('pengelola.')->group(function () {
        Route::get('/', Pengelola\DashboardController::class)->name('dashboard');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
    });
});
