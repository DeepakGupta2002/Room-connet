<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Models\DonationPublicProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DonationController extends Controller
{
    public function index(): Response
    {
        $donors = Donation::query()->with('publicProfile')->where('status', 'verified')->latest('verified_at')->limit(30)->get()->map(fn (Donation $donation): array => [
            'name' => $donation->publicProfile?->is_anonymous ? 'Anonymous supporter' : ($donation->publicProfile?->public_name ?? 'Community supporter'),
            'amount' => $donation->publicProfile?->show_amount ? $donation->amount : null,
            'verified_at' => $donation->verified_at?->toDateString(),
        ]);

        return Inertia::render('Donations', ['donors' => $donors, 'upiId' => config('roomconnect.donation_upi_id'), 'qrImage' => config('roomconnect.donation_qr_image')]);
    }

    public function store(StoreDonationRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $data = $request->validated();
                $donation = Donation::create([
                    'user_id' => $request->user()->id,
                    'amount' => $data['amount'],
                    'currency' => 'INR',
                    'payment_method' => $data['payment_method'],
                    'provider' => $data['payment_method'] === 'qr' ? 'manual_qr' : config('roomconnect.donation_provider'),
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'proof_path' => $request->file('proof')?->store('donation-proofs', 'local'),
                    'status' => 'pending',
                ]);

                DonationPublicProfile::create([
                    'donation_id' => $donation->id,
                    'public_name' => $data['public_name'] ?? null,
                    'is_anonymous' => (bool) ($data['is_anonymous'] ?? true),
                    'show_amount' => (bool) ($data['show_amount'] ?? false),
                ]);
            });

            return back()->with('status', 'Donation request create ho gayi. Payment verification ke baad donor access activate hoga.');
        } catch (Throwable $exception) {
            Log::error('Donation creation failed', ['user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['donation' => 'Donation request create nahi ho saki. Please dobara try karein.']);
        }
    }
}
