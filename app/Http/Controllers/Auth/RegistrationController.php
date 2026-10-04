<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegistrationController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(config('auth.verification.mode') === 'email', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        Auth::login($user);
        try {
            event(new Registered($user));
        } catch (Throwable $exception) {
            Log::warning('Registration email could not be sent', [
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            if ($request->header('X-Inertia')) {
                return redirect()->route('verification.notice')->with('status', 'Account create ho gaya, lekin verification email abhi send nahi ho saka. Resend button se dobara try karein.');
            }

            return response()->json([
                'message' => 'Account created, but verification email could not be sent. Please try resend.',
                'verification_required' => true,
            ], 201);
        }

        if ($request->header('X-Inertia')) {
            return redirect()->route('verification.notice');
        }

        return response()->json([
            'message' => 'Account created. Please verify your email before unlocking contacts.',
            'verification_required' => true,
        ], 201);
    }
}
