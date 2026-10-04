<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(config('auth.verification.mode') === 'email', 404);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10}$/'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        $email = strtolower(trim($validated['email']));
        $phone = '+91'.$validated['phone'];
        $phoneHash = hash_hmac('sha256', $phone, config('app.key'));
        if (User::where('email', $email)->exists()) return back()->withErrors(['email' => 'Ye email already registered hai. Login ke liye OTP request karein.'])->withInput();
        if (User::where('phone_hash', $phoneHash)->exists()) return back()->withErrors(['phone' => 'Ye phone number already registered hai.'])->withInput();

        $otp = (string) random_int(100000, 999999);
        $request->session()->put('pending_registration', [
            'name' => $validated['name'], 'email' => $email, 'phone' => $phone, 'phone_hash' => $phoneHash,
            'latitude' => $validated['latitude'] ?? null, 'longitude' => $validated['longitude'] ?? null,
            'otp_hash' => Hash::make($otp), 'expires_at' => now()->addMinutes(config('auth.verification.otp_expire'))->timestamp,
            'resend_available_at' => now()->addSeconds(config('auth.verification.otp_resend_seconds'))->timestamp, 'attempts' => 0,
        ]);
        try {
            Mail::raw("Your RoomConnect verification OTP is {$otp}. It expires in ".config('auth.verification.otp_expire')." minutes. Do not share this code.", function ($message) use ($email): void { $message->to($email)->subject('RoomConnect verification OTP'); });
        } catch (\Throwable $exception) {
            Log::warning('Registration OTP could not be sent', ['email' => $email, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['email' => 'OTP email send nahi ho saka. SMTP settings check karke dobara try karein.'])->withInput();
        }
        if ($request->header('X-Inertia')) return redirect()->route('otp.notice', ['flow' => 'register']);
        return response()->json(['message' => 'OTP sent. Please verify your email.', 'verification_required' => true], 202);
    }
}
