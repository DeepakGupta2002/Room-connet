<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyDonationRequest;
use App\Models\ActivityLog;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DonationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Donations', [
            'donations' => Donation::with(['user:id,name,email', 'publicProfile'])->latest()->paginate(20),
        ]);
    }

    public function verify(VerifyDonationRequest $request, Donation $donation): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $donation): void {
                $status = $request->validated('status');
                $until = $status === 'verified' ? now()->addDays(config('roomconnect.donor_access_duration_days')) : null;
                $donation->update(['status' => $status, 'verified_at' => $status === 'verified' ? now() : null, 'access_granted_until' => $until]);
                if ($status === 'verified') {
                    $user = $donation->user;
                    $current = $user->donor_access_until;
                    $user->update(['donor_access_until' => $current && $current->isFuture() ? $current->max($until) : $until]);
                }
                ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'donation.status_updated', 'entity_type' => Donation::class, 'entity_id' => $donation->id, 'meta_data' => ['status' => $status, 'user_id' => $donation->user_id], 'ip_address' => $request->ip(), 'created_at' => now()]);
            });
            return back()->with('status', 'Donation status update ho gaya.');
        } catch (Throwable $exception) {
            Log::error('Donation verification failed', ['donation_id' => $donation->id, 'admin_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['donation' => 'Donation verify nahi ho saki.']);
        }
    }
}
