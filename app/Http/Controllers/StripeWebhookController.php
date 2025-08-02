<?php

namespace App\Http\Controllers;

use App\Http\Services\RouteService;
use App\Http\Services\TicketPurchaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Stripe;
use App\Models\Transaction;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $signature,
                config('services.stripe.webhook_secret')
            );

            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleSuccessfulPayment($event->data->object);
                    break;
                case 'checkout.session.expired':
                    $this->handleExpiredPayment($event->data->object);
                    break;
                case 'checkout.session.async_payment_succeeded':
                    $this->handleSuccessfulPayment($event->data->object);
                    break;
                case 'checkout.session.async_payment_failed':
                    $this->handleFailedPayment($event->data->object);
                    break;
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function handleSuccessfulPayment($session)
    {
        $paymentStatusMap = [
            'paid' => 'zakończona',
            'unpaid' => 'oczekująca',
            'canceled' => 'anulowana',
            'requires_payment_method' => 'oczekująca',
            'requires_confirmation' => 'oczekująca',
            'requires_action' => 'oczekująca'
        ];

        $transactionStatus = $paymentStatusMap[$session->payment_status] ?? 'oczekująca';

        $existingTransaction = Transaction::where('payment_id', $session->id)->first();

        if ($existingTransaction) {
            $existingTransaction->status = $transactionStatus;
            $existingTransaction->save();

            $purchasedTicketStatus = match($transactionStatus) {
                'zakończona' => 'aktywny',
                'oczekująca' => 'oczekujący',
                'anulowana' => 'anulowany',
                'wygasła' => 'wygasły',
                'nieudana' => 'anulowany',
                default => 'oczekujący'
            };

            foreach ($existingTransaction->purchasedTickets as $ticket) {
                $ticket->status = $purchasedTicketStatus;
                $ticket->save();
            }
            return;
        }

        $routeData = null;
        if (!empty($session->metadata->from_stop_id) && !empty($session->metadata->to_stop_id)) {
            $routeService = app(RouteService::class);
            $routes = $routeService->findRoutes(
                $session->metadata->from_stop_id,
                $session->metadata->to_stop_id,
                Carbon::now(),
                false
            );

            if (!empty($routes)) {
                $routeData = $routes[0];
            }
        }

        $ticketPurchaseService = app(TicketPurchaseService::class);
        $ticketPurchaseService->createTicketPurchase(
            $session->metadata->ticket_id,
            $session->metadata->user_id,
            $session->id,
            $session->metadata->from_stop_id ?? null,
            $session->metadata->to_stop_id ?? null,
            $session->metadata->line_id ?? null,
            $session->amount_total / 100,
            $session->metadata->duration ?? 0,
            $routeData,
            $transactionStatus
        );
    }

    private function handleExpiredPayment($session)
    {
        $transaction = Transaction::where('payment_id', $session->id)->first();

        if ($transaction) {
            $transaction->status = 'wygasła';
            $transaction->save();

            foreach ($transaction->purchasedTickets as $ticket) {
                $ticket->status = 'wygasły';
                $ticket->save();
            }
        }
    }

    private function handleFailedPayment($session)
    {
        $transaction = Transaction::where('payment_id', $session->id)->first();

        if ($transaction) {
            $transaction->status = 'nieudana';
            $transaction->save();

            foreach ($transaction->purchasedTickets as $ticket) {
                $ticket->status = 'anulowany';
                $ticket->save();
            }
        }
    }
}
