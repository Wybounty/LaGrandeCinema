<?php

namespace App\Http\Controllers;

use App\PaymentStatus;
use App\Support\StripePaymentCompleter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripePaymentCompleter $completer): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');
        $secret = config('stripe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            Log::warning('Stripe webhook secret is not configured.');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException) {
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException) {
            return response('Invalid payload', 400);
        }

        $session = $event->data->object;
        $sessionId = (string) ($session->id ?? '');

        match ($event->type) {
            'checkout.session.completed' => ($session->payment_status ?? null) === 'paid'
                ? $completer->processPaidCheckoutSession($session)
                : null,
            'checkout.session.expired' => $completer->markPendingSession($sessionId, PaymentStatus::Cancelled),
            'checkout.session.async_payment_failed' => $completer->markPendingSession($sessionId, PaymentStatus::Failed),
            default => null,
        };

        return response('OK', 200);
    }
}
