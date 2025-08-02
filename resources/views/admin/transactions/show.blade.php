@extends('layouts.app')

@section('page_title', 'Szczegóły transakcji: ' . $transaction->transaction_id)

@section('content')
    <div class="container">
        <h1>Szczegóły transakcji: {{ $transaction->transaction_id }}</h1>
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informacje o transakcji</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">ID transakcji:</div>
                    <div class="col-md-8">{{ $transaction->transaction_id }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Użytkownik:</div>
                    <div class="col-md-8">{{ $transaction->user->name ?? 'N/A' }} (ID: {{ $transaction->user->user_id ?? 'N/A' }})</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Kwota:</div>
                    <div class="col-md-8">{{ number_format($transaction->price, 2) }} zł</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Status:</div>
                    <div class="col-md-8">
                        @php
                            $statusClass = match(strtolower($transaction->status)) {
                                'zakończona' => 'success',
                                'oczekująca' => 'warning',
                                'anulowana' => 'secondary',
                                'wygasła' => 'dark',
                                'nieudana' => 'danger',
                                default => 'info',
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Data transakcji:</div>
                    <div class="col-md-8">{{ $transaction->created_at->format('d.m.Y H:i:s') }}</div>
                </div>
                @if($transaction->fromStop && $transaction->toStop)
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Trasa:</div>
                        <div class="col-md-8">
                            {{ $transaction->fromStop->stop_name }} →
                            {{ $transaction->toStop->stop_name }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Linia:</div>
                        <div class="col-md-8">{{ $transaction->line->line_number ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Czas przejazdu:</div>
                        <div class="col-md-8">{{ $transaction->duration_minutes }} min</div>
                    </div>
                @endif
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">ID płatności (Stripe/inne):</div>
                    <div class="col-md-8">{{ $transaction->payment_id ?? 'N/A' }}</div>
                </div>
                <hr>
                <h5 class="mb-3">Powiązane zakupione bilety</h5>
                @if($transaction->purchasedTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                            <tr>
                                <th>ID zakupu</th>
                                <th>Typ biletu</th>
                                <th>Ważny od</th>
                                <th>Ważny do</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($transaction->purchasedTickets as $purchasedTicket)
                                <tr>
                                    <td>{{ $purchasedTicket->purchase_id }}</td>
                                    <td>{{ $purchasedTicket->ticket->ticket_name ?? 'N/A' }}</td>
                                    <td>{{ $purchasedTicket->valid_from->format('d.m.Y H:i') }}</td>
                                    <td>{{ $purchasedTicket->valid_until->format('d.m.Y H:i') }}</td>
                                    <td>
                                        @php
                                            $ticketStatusText = ucfirst($purchasedTicket->status);
                                            $ticketStatusClass = 'secondary';
                                            if ($purchasedTicket->status === 'aktywny') {
                                                if ($purchasedTicket->valid_until > now()) {
                                                    $ticketStatusText = 'Aktywny';
                                                    $ticketStatusClass = 'success';
                                                } else {
                                                    $ticketStatusText = 'Wygasły';
                                                    $ticketStatusClass = 'danger';
                                                }
                                            } elseif ($purchasedTicket->status === 'oczekujący') {
                                                $ticketStatusText = 'Oczekujący';
                                                $ticketStatusClass = 'warning';
                                            } elseif ($purchasedTicket->status === 'anulowany') {
                                                $ticketStatusText = 'Anulowany';
                                                $ticketStatusClass = 'dark';
                                            } elseif ($purchasedTicket->status === 'wygasły') {
                                                $ticketStatusText = 'Wygasły';
                                                $ticketStatusClass = 'danger';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $ticketStatusClass }}">
                                            {{ $ticketStatusText }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.purchasedtickets.show', $purchasedTicket->purchase_id) }}" class="btn btn-sm btn-info">Szczegóły</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">Brak powiązanych zakupionych biletów.</div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('dashboard', ['tab' => 'transactions']) }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Powrót do listy transakcji
                </a>
            </div>
        </div>
    </div>
@endsection
