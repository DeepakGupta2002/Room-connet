<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\HomeController;

Route::get('/', HomeController::class)->name('home');

Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login.page');
Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register.page');
Route::get('/email/verify', fn () => Inertia::render('Auth/VerifyEmail'))->middleware('auth')->name('verification.notice');
Route::get('/search', [ListingController::class, 'search'])->name('search');
Route::get('/donations', [DonationController::class, 'index'])->name('donations');
Route::post('/donations', [DonationController::class, 'store'])->middleware(['auth', 'throttle:5,1'])->name('donations.store');
Route::post('/donations/razorpay/order', [DonationController::class, 'createRazorpayOrder'])->middleware(['auth', 'throttle:5,1'])->name('donations.razorpay.order');
Route::post('/donations/razorpay/verify', [DonationController::class, 'verifyRazorpayPayment'])->middleware(['auth', 'throttle:10,1'])->name('donations.razorpay.verify');
Route::get('/post-room', fn () => Inertia::render('PostRoom', [
    'googleMapsKey' => config('services.google_maps.key'),
]))->middleware('auth')->name('post-room');
Route::post('/post-room', [PostController::class, 'store'])->middleware('auth')->name('post-room.store');
Route::get('/my-listings', [ListingController::class, 'mine'])->middleware('auth')->name('my-listings');
Route::middleware('auth')->group(function (): void {
    Route::get('/my-listings/{post:slug}/edit', [PostController::class, 'edit'])->name('my-listings.edit');
    Route::put('/my-listings/{post:slug}', [PostController::class, 'update'])->name('my-listings.update');
});
Route::get('/rooms/{post:slug}', [RoomController::class, 'show'])->name('rooms.show');
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/rooms/{post:slug}/contact', [RoomController::class, 'contact'])->name('rooms.contact');
    Route::post('/rooms/{post:slug}/contact/unlock', [RoomController::class, 'unlock'])->middleware('throttle:30,1')->name('rooms.contact.unlock');
    Route::post('/rooms/{post:slug}/report', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('rooms.report');
});

Route::middleware(['auth', 'role:admin,moderator'])->prefix('admin')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/moderation', [ModerationController::class, 'index'])->name('admin.moderation');
    Route::patch('/moderation/{report}', [ModerationController::class, 'update'])->name('admin.moderation.update');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs');
    Route::get('/donations', [AdminDonationController::class, 'index'])->name('admin.donations');
    Route::patch('/donations/{donation}', [AdminDonationController::class, 'verify'])->name('admin.donations.verify');
});

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
