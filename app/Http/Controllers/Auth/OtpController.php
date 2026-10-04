<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class OtpController extends Controller
{
    public function show(Request $request)
    {
        $flow = $request->query('flow', 'register');
        abort_unless(in_array($flow, ['register', 'login'], true), 404);
        $key = $flow === 'register' ? 'pending_registration' : 'pending_login';
        abort_unless($request->session()->has($key), 403);
        $pending = $request->session()->get($key);
        return Inertia::render('Auth/VerifyOtp', ['flow' => $flow, 'email' => $pending['email']]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['flow' => ['required', 'in:register,login'], 'otp' => ['required', 'digits:6']]);
        $key = $data['flow'] === 'register' ? 'pending_registration' : 'pending_login';
        $pending = $request->session()->get($key);
        abort_unless($pending, 403);
        if ($pending['expires_at'] < now()->timestamp) return back()->withErrors(['otp' => 'This OTP has expired. Request a new OTP.']);
        if (($pending['attempts'] ?? 0) >= 5) return back()->withErrors(['otp' => 'Maximum attempts reached. Request a new OTP.']);
        if (! Hash::check($data['otp'], $pending['otp_hash'])) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1; $request->session()->put($key, $pending);
            return back()->withErrors(['otp' => 'The OTP is incorrect.']);
        }
        if ($data['flow'] === 'register') {
            $user = DB::transaction(function () use ($pending): User {
                $user = User::create(['name' => $pending['name'], 'email' => $pending['email'], 'password' => null, 'email_verified_at' => now(), 'phone_encrypted' => $pending['phone'], 'phone_hash' => $pending['phone_hash'], 'phone_verified_at' => now(), 'status' => 'active', 'latitude' => $pending['latitude'], 'longitude' => $pending['longitude']]);
                $roleId = Role::where('name', 'seeker')->value('id'); if ($roleId) $user->roles()->attach($roleId);
                return $user;
            });
        } else { $user = User::findOrFail($pending['user_id']); }
        $request->session()->forget($key); Auth::login($user); $request->session()->regenerate(); $user->forceFill(['last_login_at' => now()])->save();
        return redirect()->intended('/')->with('status', $data['flow'] === 'register' ? 'Your account has been verified.' : 'Login successful.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $data = $request->validate(['flow' => ['required', 'in:register,login']]); $key = $data['flow'] === 'register' ? 'pending_registration' : 'pending_login'; $pending = $request->session()->get($key); abort_unless($pending, 403);
        if (($pending['resend_available_at'] ?? 0) > now()->timestamp) return back()->withErrors(['otp' => 'Resend will be available shortly.']);
        $otp = (string) random_int(100000, 999999); $pending['otp_hash'] = Hash::make($otp); $pending['expires_at'] = now()->addMinutes(config('auth.verification.otp_expire'))->timestamp; $pending['resend_available_at'] = now()->addSeconds(config('auth.verification.otp_resend_seconds'))->timestamp; $pending['attempts'] = 0; $request->session()->put($key, $pending);
        try { Mail::raw("Your RoomConnect verification OTP is {$otp}. It expires in ".config('auth.verification.otp_expire')." minutes. Do not share this code.", function ($message) use ($pending): void { $message->to($pending['email'])->subject('RoomConnect verification OTP'); }); } catch (\Throwable $exception) { Log::warning('OTP resend failed', ['exception' => $exception->getMessage()]); return back()->withErrors(['otp' => 'We could not resend the OTP.']); }
        return back()->with('status', 'A new OTP has been sent to your email.');
    }
}
