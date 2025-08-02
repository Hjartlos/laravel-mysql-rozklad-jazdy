<?php

namespace App\Http\Services;

use App\Models\PurchasedTicket;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Stop;
use Carbon\Carbon;

class TicketPurchaseService
{
    public function createTicketPurchase($ticketId, $userId, $sessionId, $fromStopId, $toStopId, $lineId, $price, $durationMinutes, $routeData = null, $paymentStatus = 'zakończona')
    {
        $ticket = Ticket::findOrFail($ticketId);
        $validFrom = Carbon::now();
        $validUntil = (clone $validFrom)->addHours($ticket->validity_hours);
        $arrivalTime = (clone $validFrom)->addMinutes($durationMinutes);

        if (!$fromStopId || !$toStopId) {
            $defaultStop = Stop::first();
            $fromStopId = $toStopId = $defaultStop ? $defaultStop->stop_id : 1;
        }

        $polishPaymentStatus = match(strtolower($paymentStatus)) {
            'completed' => 'zakończona',
            'pending' => 'oczekująca',
            'canceled', 'cancelled' => 'anulowana',
            'expired' => 'wygasła',
            'failed' => 'nieudana',
            default => $paymentStatus
        };

        $transaction = Transaction::create([
            'user_id' => $userId,
            'from_stop_id' => $fromStopId,
            'to_stop_id' => $toStopId,
            'line_id' => $lineId,
            'departure_time' => Carbon::now(),
            'arrival_time' => $arrivalTime,
            'price' => $price,
            'status' => $polishPaymentStatus,
            'payment_id' => $sessionId,
            'duration_minutes' => $durationMinutes,
            'route_data' => $routeData,
        ]);

        $purchasedTicketStatus = match($polishPaymentStatus) {
            'zakończona' => 'aktywny',
            'oczekująca' => 'oczekujący',
            'anulowana' => 'anulowany',
            'wygasła' => 'wygasły',
            'nieudana' => 'anulowany',
            default => 'oczekujący'
        };

        return PurchasedTicket::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $userId,
            'transaction_id' => $transaction->transaction_id,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'status' => $purchasedTicketStatus
        ]);
    }
}
