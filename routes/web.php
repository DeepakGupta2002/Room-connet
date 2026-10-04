<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ListingController;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login.page');
Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register.page');
Route::get('/email/verify', fn () => Inertia::render('Auth/VerifyEmail'))->middleware('auth')->name('verification.notice');
Route::get('/search', [ListingController::class, 'search'])->name('search');
Route::get('/post-room', fn () => Inertia::render('PostRoom', [
    'googleMapsKey' => config('services.google_maps.key'),
]))->middleware('auth')->name('post-room');
Route::post('/post-room', [PostController::class, 'store'])->middleware('auth')->name('post-room.store');
Route::get('/my-listings', [ListingController::class, 'mine'])->middleware('auth')->name('my-listings');

Route::post('/auth/register', [RegistrationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('register');

Route::post('/auth/login', [SessionController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('login');

Route::post('/auth/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
