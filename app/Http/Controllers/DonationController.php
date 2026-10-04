<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Http\Requests\CreateRazorpayOrderRequest;
use App\Http\Requests\VerifyRazorpayPaymentRequest;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Models\DonationPublicProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\RazorpayPaymentService;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DonationController extends Controller
{
    public function createRazorpayOrder(CreateRazorpayOrderRequest $request, RazorpayPaymentService $razorpay): JsonResponse
    {
        try {
            $data = $request->validated();
            $donation = DB::transaction(function () use ($request, $data): Donation {
                $donation = Donation::create(['user_id' => $request->user()->id, 'amount' => $data['amount'], 'currency' => 'INR', 'payment_method' => 'razorpay', 'provider' => 'razorpay', 'status' => 'pending']);
                DonationPublicProfile::create(['donation_id' => $donation->id, 'public_name' => $data['public_name'] ?? null, 'is_anonymous' => (bool) ($data['is_anonymous'] ?? true), 'show_amount' => (bool) ($data['show_amount'] ?? false)]);
                return $donation;
            });
            $order = $razorpay->createOrder((int) round(((float) $donation->amount) * 100), 'donation_'.$donation->id, ['donation_id' => (string) $donation->id]);
            $donation->update(['order_id' => $order['id']]);
            return response()->json(['key_id' => config('services.razorpay.key_id'), 'order_id' => $order['id'], 'amount' => $order['amount'], 'currency' => $order['currency'], 'donation_id' => $donation->id]);
        } catch (Throwable $exception) {
            Log::error('Razorpay order creation failed', ['user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return response()->json(['message' => 'Online payment start nahi ho saka. QR payment try karein.'], 502);
        }
    }

    public function verifyRazorpayPayment(VerifyRazorpayPaymentRequest $request, RazorpayPaymentService $razorpay): JsonResponse
    {
        try {
            $data = $request->validated();
            $donation = Donation::whereKey($data['donation_id'])->where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            if ($donation->status === 'verified') return response()->json(['message' => 'Payment already verified.']);
            if (! hash_equals((string) $donation->order_id, (string) $data['razorpay_order_id'])) return response()->json(['message' => 'Order mismatch.'], 422);
            $expected = hash_hmac('sha256', $donation->order_id.'|'.$data['razorpay_payment_id'], (string) config('services.razorpay.key_secret'));
            if (! hash_equals($expected, $data['razorpay_signature'])) return response()->json(['message' => 'Payment signature invalid.'], 422);
            $payment = $razorpay->fetchPayment($data['razorpay_payment_id']);
            if (($payment['status'] ?? null) !== 'captured' || (int) ($payment['amount'] ?? 0) !== (int) round(((float) $donation->amount) * 100)) return response()->json(['message' => 'Payment captured status verify nahi hua.'], 422);
            DB::transaction(function () use ($request, $donation, $data): void {
                $until = now()->addDays(config('roomconnect.donor_access_duration_days'));
                $donation->update(['payment_id' => $data['razorpay_payment_id'], 'status' => 'verified', 'verified_at' => now(), 'access_granted_until' => $until]);
                $user = $request->user();
                $user->update(['donor_access_until' => $user->donor_access_until?->isFuture() ? $user->donor_access_until->max($until) : $until]);
                ActivityLog::create(['user_id' => $user->id, 'action' => 'donation.razorpay_verified', 'entity_type' => Donation::class, 'entity_id' => $donation->id, 'meta_data' => ['payment_id' => $data['razorpay_payment_id']], 'ip_address' => $request->ip(), 'created_at' => now()]);
            });
            return response()->json(['message' => 'Payment verified. Donor access activated.']);
        } catch (Throwable $exception) {
            Log::error('Razorpay payment verification failed', ['user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return response()->json(['message' => 'Payment verify nahi ho saka. Support se contact karein.'], 502);
        }
    }

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

            return back()->with('status', 'Donation request created. Donor access will activate after payment verification.');
        } catch (Throwable $exception) {
            Log::error('Donation creation failed', ['user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['donation' => 'Donation request could not be created. Please try again.']);
        }
    }
}
