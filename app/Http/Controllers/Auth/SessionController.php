<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(config('auth.verification.mode') === 'email', 404);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['email' => 'Invalid email or password.']);
            }

            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        if ($request->header('X-Inertia')) {
            return redirect()->intended('/');
        }

        return response()->json([
            'message' => 'Login successful.',
            'email_verified' => $request->user()->hasVerifiedEmail(),
            'verification_required' => $request->user()->requiresEmailVerification() && ! $request->user()->hasVerifiedEmail(),
        ]);
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->header('X-Inertia')) {
            return redirect('/');
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
