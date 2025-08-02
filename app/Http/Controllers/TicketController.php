<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketCalculationRequest;
use App\Http\Requests\TicketSuccessRequest;
use App\Http\Services\TicketPurchaseService;
use App\Http\Services\RouteService;
use App\Models\LineStop;
use App\Models\Line;
use App\Models\Stop;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\PurchasedTicket;
use App\Http\Services\TicketPriceService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Http\Request;

class TicketController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index', 'show']),
        ];
    }

    public function index()
    {
        $tickets = Ticket::where('is_active', true)->get();
        return view('tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);
        return view('tickets.show', compact('ticket'));
    }

    public function myTickets()
    {
        $activeTickets = PurchasedTicket::where('user_id', Auth::id())
            ->where('status', 'aktywny')
            ->where('valid_until', '>=', now())
            ->with('ticket')
            ->orderBy('valid_until')
            ->get();

        $expiredTickets = PurchasedTicket::where('user_id', Auth::id())
            ->where(function($query) {
                $query->where('status', 'wygasły')
                    ->orWhere('valid_until', '<', now());
            })
            ->with('ticket')
            ->orderBy('valid_until', 'desc')
            ->get();

        return view('tickets.my-tickets', compact('activeTickets', 'expiredTickets'));
    }

    public function selectRoute()
    {
        $tickets = Ticket::where('is_active', true)->get();
        $stops = Stop::all();

        return view('tickets.route-selection', compact('tickets', 'stops'));
    }

    public function calculate(TicketCalculationRequest $request)
    {
        $fromStop = Stop::findOrFail($request->from_stop_id);
        $toStop = Stop::findOrFail($request->to_stop_id);
        $line = Line::findOrFail($request->line_id);
        $ticket = Ticket::findOrFail($request->ticket_id);

        $fromSequence = LineStop::where('line_id', $line->line_id)
            ->where('stop_id', $fromStop->stop_id)
            ->first()->sequence;

        $toSequence = LineStop::where('line_id', $line->line_id)
            ->where('stop_id', $toStop->stop_id)
            ->first()->sequence;

        if ($fromSequence >= $toSequence) {
            return back()->withErrors([
                'from_stop_id' => 'Wybrany kierunek podróży jest nieprawidłowy.'
            ])->withInput();
        }

        $distanceInput = $request->input('route_distance');
        $distance = null;
        $ticketPriceService = app(TicketPriceService::class);

        if ($distanceInput !== null && $distanceInput !== '' && is_numeric(str_replace(',', '.', $distanceInput))) {
            $parsedDistance = floatval(str_replace(',', '.', $distanceInput));
            if ($parsedDistance > 0 || ($parsedDistance === 0.0 && $fromStop->stop_id === $toStop->stop_id)) {
                $distance = $parsedDistance;
            }
        }

        if ($distance === null) {
            $distance = $ticketPriceService->calculateRouteDistance(
                $fromStop->stop_id,
                $toStop->stop_id,
                $line->line_id
            );
        }

        $avgTimePerSegment = $line->avg_time_per_segment_minutes ?? 3;
        $durationMinutes = ($toSequence - $fromSequence) * $avgTimePerSegment;
        if ($fromStop->stop_id === $toStop->stop_id) {
            $durationMinutes = 0;
        }
        $durationMinutes = max(0, $durationMinutes);

        $calculatedPrice = $ticketPriceService->calculatePrice(
            $durationMinutes,
            $distance,
            $ticket->ticket_id
        );

        $finalPrice = ceil($calculatedPrice);

        $routeData = [
            'from_stop' => [
                'id' => $fromStop->stop_id,
                'name' => $fromStop->stop_name,
                'sequence' => $fromSequence,
                'location_lat' => $fromStop->location_lat,
                'location_lon' => $fromStop->location_lon
            ],
            'to_stop' => [
                'id' => $toStop->stop_id,
                'name' => $toStop->stop_name,
                'sequence' => $toSequence,
                'location_lat' => $toStop->location_lat,
                'location_lon' => $toStop->location_lon
            ],
            'line' => [
                'id' => $line->line_id,
                'number' => $line->line_number,
                'name' => $line->line_name
            ],
            'distance' => round($distance, 2),
            'duration' => $durationMinutes
        ];

        session([
            'route_data' => $routeData,
            'ticket_id' => $ticket->ticket_id,
            'calculated_price' => $finalPrice
        ]);

        return redirect()->route('tickets.checkout-route');
    }

    public function checkoutRoute()
    {
        $routeData = session('route_data');
        $ticketId = session('ticket_id');
        $calculatedPrice = session('calculated_price');

        if (!$routeData || !$ticketId || !$calculatedPrice) {
            return redirect()->route('tickets.select-route')
                ->with('error', 'Brakuje danych trasy. Wybierz trasę ponownie.');
        }

        $ticket = Ticket::findOrFail($ticketId);
        $ticket->price = $calculatedPrice;

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'pln',
                    'product_data' => [
                        'name' => $ticket->ticket_name . ' (' .
                            $routeData['from_stop']['name'] . ' - ' .
                            $routeData['to_stop']['name'] . ', Linia ' .
                            $routeData['line']['number'] . ')'
                    ],
                    'unit_amount' => $calculatedPrice * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('tickets.success') . '?session_id={CHECKOUT_SESSION_ID}&ticket_id=' . $ticket->ticket_id,
            'cancel_url' => route('tickets.cancel') . '?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => [
                'ticket_id' => $ticket->ticket_id,
                'user_id' => Auth::id(),
                'from_stop_id' => $routeData['from_stop']['id'],
                'to_stop_id' => $routeData['to_stop']['id'],
                'line_id' => $routeData['line']['id'],
                'distance' => $routeData['distance'],
                'duration' => $routeData['duration']
            ],
        ]);

        $durationMinutes = $routeData['duration'];
        $arrivalTime = Carbon::now()->addMinutes($durationMinutes);

        $ticketPurchaseService = app(TicketPurchaseService::class);
        $purchasedTicket = $ticketPurchaseService->createTicketPurchase(
            $ticket->ticket_id,
            Auth::id(),
            $session->id,
            $routeData['from_stop']['id'],
            $routeData['to_stop']['id'],
            $routeData['line']['id'],
            $calculatedPrice,
            $durationMinutes,
            $routeData,
            'oczekująca'
        );

        session(['stripe_session_id' => $session->id]);

        return view('tickets.checkout', [
            'ticket' => $ticket,
            'sessionId' => $session->id,
            'routeData' => $routeData
        ]);
    }

    public function cancel(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $transaction = Transaction::where('payment_id', $sessionId)->first();

            if ($transaction) {
                $transaction->status = 'anulowana';
                $transaction->save();

                foreach ($transaction->purchasedTickets as $ticket) {
                    $ticket->status = 'anulowany';
                    $ticket->save();
                }

                return redirect()->route('tickets.index')
                    ->with('warning', 'Płatność została anulowana. Możesz spróbować ponownie.');
            }
        }

        return redirect()->route('tickets.index')
            ->with('warning', 'Płatność została anulowana.');
    }

    public function showTicket($purchaseId)
    {
        $purchasedTicket = PurchasedTicket::with([
            'ticket',
            'user',
            'transaction.fromStop',
            'transaction.toStop',
            'transaction.line'
        ])->findOrFail($purchaseId);

        if ($purchasedTicket->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return redirect()->route('tickets.my-tickets')
                ->with('error', 'Nie masz dostępu do tego biletu.');
        }

        $intermediateStopsData = null;
        if ($purchasedTicket->transaction &&
            $purchasedTicket->transaction->line_id &&
            $purchasedTicket->transaction->from_stop_id &&
            $purchasedTicket->transaction->to_stop_id) {

            $line = $purchasedTicket->transaction->line;
            $fromStopId = $purchasedTicket->transaction->from_stop_id;
            $toStopId = $purchasedTicket->transaction->to_stop_id;

            $fromLineStop = LineStop::where('line_id', $line->line_id)
                ->where('stop_id', $fromStopId)
                ->first();

            $toLineStop = LineStop::where('line_id', $line->line_id)
                ->where('stop_id', $toStopId)
                ->first();

            if ($fromLineStop && $toLineStop && $fromLineStop->sequence < $toLineStop->sequence) {
                $intermediateLineStops = LineStop::where('line_id', $line->line_id)
                    ->where('sequence', '>=', $fromLineStop->sequence)
                    ->where('sequence', '<=', $toLineStop->sequence)
                    ->orderBy('sequence')
                    ->with('stop:stop_id,stop_name,location_lat,location_lon')
                    ->get();

                $intermediateStopsData = $intermediateLineStops->map(function ($lineStop) use ($fromStopId, $toStopId) {
                    return [
                        'stop_id'      => $lineStop->stop->stop_id,
                        'stop_name'    => $lineStop->stop->stop_name,
                        'location_lat' => $lineStop->stop->location_lat,
                        'location_lon' => $lineStop->stop->location_lon,
                        'sequence'     => $lineStop->sequence,
                        'is_start'     => $lineStop->stop->stop_id == $fromStopId,
                        'is_end'       => $lineStop->stop->stop_id == $toStopId,
                    ];
                });
            }
        }

        return view('tickets.show-ticket', compact('purchasedTicket', 'intermediateStopsData'));
    }

    public function success(TicketSuccessRequest $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('tickets.index')->with('error', 'Brak wymaganych danych płatności.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        $paymentStatusMap = [
            'paid' => 'zakończona',
            'unpaid' => 'oczekująca',
            'canceled' => 'anulowana',
            'requires_payment_method' => 'oczekująca',
            'requires_confirmation' => 'oczekująca',
            'requires_action' => 'oczekująca'
        ];

        $transactionStatus = $paymentStatusMap[$session->payment_status] ?? 'oczekująca';

        if ($transactionStatus !== 'zakończona') {
            return redirect()->route('tickets.index')
                ->with('error', 'Płatność ma status: ' . $session->payment_status . '. Spróbuj ponownie.');
        }

        $transaction = Transaction::where('payment_id', $sessionId)->first();
        if ($transaction) {
            $transaction->status = 'zakończona';
            $transaction->save();

            foreach ($transaction->purchasedTickets as $ticket) {
                $ticket->status = 'aktywny';
                $ticket->save();
            }
        }

        return redirect()->route('tickets.my-tickets')
            ->with('success', 'Bilet został zakupiony pomyślnie.');
    }
}
