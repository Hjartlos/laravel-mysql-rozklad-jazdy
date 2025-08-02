@extends('layouts.app')

@section('page_title', 'Bilet: ' . $ticket->ticket_name)

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card auto-height-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ $ticket->ticket_name }}</h5>
                    </div>
                    <div class="card-body auto-height">
                        <p>{{ $ticket->description }}</p>
                        <div class="fs-3 text-center my-3">{{ number_format($ticket->price, 2) }} zł</div>
                        <p class="text-muted text-center">
                            Ważny przez {{ $ticket->validity_hours }}
                            {{ trans_choice('godz.|godz.|godz.', $ticket->validity_hours) }}
                        </p>

                        <div class="text-center mt-4">
                            @auth
                                <a href="{{ route('tickets.checkout', ['id' => $ticket->ticket_id, 'type' => 'standard']) }}" class="btn btn-primary">
                                    Kup teraz
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    Zaloguj się, aby kupić
                                </a>
                            @endauth

                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary ms-2">
                                Wróć do listy biletów
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
