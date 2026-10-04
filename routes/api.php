<?php

use App\Http\Controllers\Webhook\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');
