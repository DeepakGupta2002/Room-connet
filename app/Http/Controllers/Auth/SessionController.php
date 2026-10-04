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

class SessionController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(config('auth.verification.mode') === 'email', 404);
        $data = $request->validate(['identifier' => ['required', 'string', 'max:255']]);
        $identifier = trim($data['identifier']);
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::where('email', strtolower($identifier))->first()
            : User::where('phone_hash', hash_hmac('sha256', str_starts_with($identifier, '+') ? $identifier : '+91'.$identifier, config('app.key')))->first();
        if (! $user) return back()->withErrors(['identifier' => 'Account nahi mila. Pehle register karein.'])->withInput();
        $email = $user->email;
        $otp = (string) random_int(100000, 999999);
        $request->session()->put('pending_login', ['user_id' => $user->id, 'email' => $email, 'otp_hash' => Hash::make($otp), 'expires_at' => now()->addMinutes(config('auth.verification.otp_expire'))->timestamp, 'resend_available_at' => now()->addSeconds(config('auth.verification.otp_resend_seconds'))->timestamp, 'attempts' => 0]);
        try {
            Mail::raw("Your RoomConnect login OTP is {$otp}. It expires in ".config('auth.verification.otp_expire')." minutes. Do not share this code.", function ($message) use ($email): void { $message->to($email)->subject('RoomConnect login OTP'); });
        } catch (\Throwable $exception) {
            Log::warning('Login OTP could not be sent', ['exception' => $exception->getMessage()]);
            return back()->withErrors(['identifier' => 'OTP email send nahi ho saka. SMTP settings check karein.'])->withInput();
        }
        if ($request->header('X-Inertia')) return redirect()->route('otp.notice', ['flow' => 'login']);
        return response()->json(['message' => 'OTP sent.'], 202);
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        auth()->logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        if ($request->header('X-Inertia')) return redirect('/');
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
