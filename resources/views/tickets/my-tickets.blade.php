@extends('layouts.app')

@section('page_title', 'Moje bilety')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h2>Moje bilety</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card auto-height-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Aktywne bilety</h5>
                    </div>
                    <div class="card-body auto-height">
                        @if($activeTickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Typ biletu</th>
                                        <th>Ważny od</th>
                                        <th>Ważny do</th>
                                        <th>Status</th>
                                        <th>Akcje</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($activeTickets as $purchasedTicket)
                                        <tr>
                                            <td>{{ $purchasedTicket->ticket->ticket_name }}</td>
                                            <td>{{ $purchasedTicket->valid_from->format('d.m.Y H:i') }}</td>
                                            <td>{{ $purchasedTicket->valid_until->format('d.m.Y H:i') }}</td>
                                            <td><span class="badge bg-success">Aktywny</span></td>
                                            <td>
                                                <a href="{{ route('tickets.show-ticket', $purchasedTicket->purchase_id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Szczegóły biletu
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                Nie posiadasz aktywnych biletów.
                                <a href="{{ route('tickets.index') }}" class="alert-link">Kup bilet</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card auto-height-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Historia biletów</h5>
                    </div>
                    <div class="card-body auto-height">
                        @if($expiredTickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Typ biletu</th>
                                        <th>Data zakupu</th>
                                        <th>Ważny do</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($expiredTickets as $purchasedTicket)
                                        <tr>
                                            <td>{{ $purchasedTicket->ticket->ticket_name }}</td>
                                            <td>{{ $purchasedTicket->created_at->format('d.m.Y H:i') }}</td>
                                            <td>{{ $purchasedTicket->valid_until->format('d.m.Y H:i') }}</td>
                                            <td><span class="badge bg-secondary">Wygasł</span></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0">Brak historii zakupionych biletów.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
