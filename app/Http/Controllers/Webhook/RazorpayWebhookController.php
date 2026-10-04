<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Models\PaymentWebhookEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $rawPayload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');
        $secret = (string) config('services.razorpay.webhook_secret');

        if ($secret === '' || $signature === '' || ! hash_equals(hash_hmac('sha256', $rawPayload, $secret), $signature)) {
            Log::warning('Invalid Razorpay webhook signature', ['ip' => $request->ip()]);
            return response('Invalid signature', 401);
        }

        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) return response('Invalid payload', 400);

        $eventId = (string) ($request->header('x-razorpay-event-id') ?: hash('sha256', $rawPayload));
        $eventName = (string) ($payload['event'] ?? 'unknown');

        try {
            $event = PaymentWebhookEvent::firstOrCreate(
                ['event_id' => $eventId],
                ['provider' => 'razorpay', 'event_name' => $eventName, 'payload' => $payload, 'signature' => $signature, 'status' => 'received'],
            );

            if ($event->status === 'processed') return response('Already processed', 200);

            DB::transaction(function () use ($payload, $event): void {
                $payment = data_get($payload, 'payload.payment.entity', []);
                $orderId = data_get($payment, 'order_id');
                $paymentId = data_get($payment, 'id');
                $donation = $orderId ? Donation::where('order_id', $orderId)->lockForUpdate()->first() : null;

                if (! $donation) {
                    $event->update(['status' => 'ignored', 'error_message' => 'Donation order not found', 'processed_at' => now()]);
                    return;
                }

                $amountPaise = (int) round(((float) $donation->amount) * 100);
                if ((int) data_get($payment, 'amount', 0) !== $amountPaise) {
                    $event->update(['status' => 'rejected', 'error_message' => 'Payment amount mismatch', 'processed_at' => now()]);
                    return;
                }

                $isCaptured = in_array($event->event_name, ['payment.captured', 'order.paid'], true);
                $donation->update([
                    'payment_id' => $paymentId,
                    'status' => $isCaptured ? 'verified' : 'failed',
                    'verified_at' => $isCaptured ? now() : null,
                    'access_granted_until' => $isCaptured ? now()->addDays(config('roomconnect.donor_access_duration_days')) : null,
                ]);

                if ($isCaptured) {
                    $user = $donation->user;
                    $until = $donation->access_granted_until;
                    $user->update(['donor_access_until' => $user->donor_access_until?->isFuture() ? $user->donor_access_until->max($until) : $until]);
                }

                $event->update(['status' => 'processed', 'processed_at' => now()]);
                ActivityLog::create(['user_id' => $donation->user_id, 'action' => 'donation.webhook_processed', 'entity_type' => Donation::class, 'entity_id' => $donation->id, 'meta_data' => ['event' => $event->event_name, 'status' => $donation->status], 'created_at' => now()]);
            });

            return response('Webhook received', 200);
        } catch (Throwable $exception) {
            Log::error('Razorpay webhook processing failed', ['event_id' => $eventId, 'exception' => $exception->getMessage()]);
            PaymentWebhookEvent::where('event_id', $eventId)->update(['status' => 'failed', 'error_message' => 'Processing exception', 'processed_at' => now()]);
            return response('Webhook processing failed', 500);
        }
    }
}
