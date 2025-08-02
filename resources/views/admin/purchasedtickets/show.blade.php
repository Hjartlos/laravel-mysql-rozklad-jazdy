@extends('layouts.app')

@section('page_title', 'Szczegóły biletu: #' . $purchasedTicket->purchase_id)

@section('content')
    <div class="container">
        <h1>Szczegóły zakupionego biletu: {{ $purchasedTicket->purchase_id }}</h1>
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informacje o bilecie</h5>
            </div>
            <div class="card-body">
                @php
                    $finalStatusText = '';
                    $finalStatusClass = '';
                    if ($purchasedTicket->status === 'aktywny') {
                        if ($purchasedTicket->valid_until > now()) {
                            $finalStatusText = 'Aktywny';
                            $finalStatusClass = 'success';
                        } else {
                            $finalStatusText = 'Wygasły';
                            $finalStatusClass = 'danger';
                        }
                    } elseif ($purchasedTicket->status === 'oczekujący') {
                        $finalStatusText = 'Oczekujący';
                        $finalStatusClass = 'warning';
                    } elseif ($purchasedTicket->status === 'anulowany') {
                        $finalStatusText = 'Anulowany';
                        $finalStatusClass = 'dark';
                    } elseif ($purchasedTicket->status === 'wygasły') {
                        $finalStatusText = 'Wygasły';
                        $finalStatusClass = 'danger';
                    } else {
                        $finalStatusText = ucfirst($purchasedTicket->status);
                        $finalStatusClass = 'secondary';
                    }
                @endphp
                <div class="text-center mb-3">
                    <span class="badge bg-{{ $finalStatusClass }} fs-6 p-2">
                        {{ $finalStatusText }}
                    </span>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">ID zakupu:</div>
                    <div class="col-md-8">{{ $purchasedTicket->purchase_id }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Użytkownik:</div>
                    <div class="col-md-8">{{ $purchasedTicket->user->name ?? 'N/A' }} (ID: {{ $purchasedTicket->user->user_id ?? 'N/A' }})</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Typ biletu:</div>
                    <div class="col-md-8">{{ $purchasedTicket->ticket->ticket_name ?? 'N/A' }} (ID: {{ $purchasedTicket->ticket->ticket_id ?? 'N/A' }})</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Data zakupu:</div>
                    <div class="col-md-8">{{ $purchasedTicket->created_at->format('d.m.Y H:i:s') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Ważny od:</div>
                    <div class="col-md-8">{{ $purchasedTicket->valid_from->format('d.m.Y H:i:s') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Ważny do:</div>
                    <div class="col-md-8">{{ $purchasedTicket->valid_until->format('d.m.Y H:i:s') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Status (zapisany):</div>
                    <div class="col-md-8">{{ ucfirst($purchasedTicket->status) }}</div>
                </div>
                <hr>
                <h5 class="mb-3">Informacje o transakcji</h5>
                @if($purchasedTicket->transaction)
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">ID transakcji:</div>
                        <div class="col-md-8">
                            <a href="{{ route('admin.transactions.show', $purchasedTicket->transaction_id) }}">{{ $purchasedTicket->transaction->transaction_id }}</a>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Status transakcji:</div>
                        <div class="col-md-8">{{ ucfirst($purchasedTicket->transaction->status) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Kwota transakcji:</div>
                        <div class="col-md-8">{{ number_format($purchasedTicket->transaction->price, 2) }} zł</div>
                    </div>
                    @if($purchasedTicket->transaction->fromStop && $purchasedTicket->transaction->toStop)
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Trasa:</div>
                            <div class="col-md-8">
                                {{ $purchasedTicket->transaction->fromStop->stop_name }} →
                                {{ $purchasedTicket->transaction->toStop->stop_name }}
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Linia:</div>
                            <div class="col-md-8">{{ $purchasedTicket->transaction->line->line_number ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Czas przejazdu:</div>
                            <div class="col-md-8">{{ $purchasedTicket->transaction->duration_minutes }} min</div>
                        </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">ID płatności (Stripe/inne):</div>
                        <div class="col-md-8">{{ $purchasedTicket->transaction->payment_id ?? 'N/A' }}</div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">Brak powiązanej transakcji.</div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('dashboard', ['tab' => 'purchasedtickets']) }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Powrót do listy
                </a>
                <div>
                    <a href="{{ route('admin.purchasedtickets.edit', $purchasedTicket->purchase_id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edytuj
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
