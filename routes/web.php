<?php

use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PhotoController as AdminPhotoController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\GalleryController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureAdminGuest;
use App\Http\Middleware\EnsureAdminPendingTwoFactor;
use Illuminate\Support\Facades\Route;

Route::get('/', [GalleryController::class, 'index'])->name('home');
Route::get('/gallery/{gallery}', [GalleryController::class, 'show'])->name('galleries.show');
Route::get('/about', [GalleryController::class, 'about'])->name('about');
Route::get('/page/{page}', [GalleryController::class, 'page'])->name('pages.show');
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(EnsureAdminGuest::class)->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
    });

    Route::middleware(EnsureAdminPendingTwoFactor::class)->group(function () {
        Route::get('/2fa/challenge', [AdminAuthController::class, 'showChallenge'])->name('2fa.challenge');
        Route::post('/2fa/challenge', [AdminAuthController::class, 'confirmChallenge'])->name('2fa.verify');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(EnsureAdminAuthenticated::class)->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/two-factor/setup', [ProfileController::class, 'setupTwoFactor'])->name('profile.two-factor.setup');
        Route::post('/profile/two-factor/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile.two-factor.confirm');
        Route::post('/profile/two-factor/recovery-codes', [ProfileController::class, 'regenerateTwoFactorRecoveryCodes'])->name('profile.two-factor.recovery-codes');
        Route::delete('/profile/two-factor', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
        Route::get('/settings', [DashboardController::class, 'settings'])->name('settings.edit');
        Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('settings.update');
        Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
        Route::put('/tags/{tag}', [AdminTagController::class, 'update'])->name('tags.update');
        Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');

        Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
        Route::post('/security/two-factor/setup', [SecurityController::class, 'setup'])->name('security.two-factor.setup');
        Route::post('/security/two-factor/confirm', [SecurityController::class, 'confirm'])->name('security.two-factor.confirm');
        Route::post('/security/two-factor/recovery-codes', [SecurityController::class, 'regenerateRecoveryCodes'])->name('security.two-factor.recovery-codes');
        Route::delete('/security/two-factor', [SecurityController::class, 'disable'])->name('security.two-factor.disable');

        Route::resource('galleries', AdminGalleryController::class)->except(['show']);
        Route::resource('photos', AdminPhotoController::class)->except(['show']);
        Route::resource('pages', AdminPageController::class)->except(['show']);
    });
});
