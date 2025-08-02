@extends('layouts.app')

@section('page_title', 'Płatność za bilet')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card auto-height-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Realizacja płatności</h5>
                    </div>
                    <div class="card-body auto-height">
                        <div class="text-center mb-4">
                            <h4>{{ $ticket->ticket_name }}</h4>
                            @if(isset($routeData))
                                <p class="lead">
                                    Linia {{ $routeData['line']['number'] }}:
                                    {{ $routeData['from_stop']['name'] }} → {{ $routeData['to_stop']['name'] }}
                                </p>
                                <p>
                                    Dystans: {{ $routeData['distance'] }} km<br>
                                    Szacowany czas przejazdu: {{ $routeData['duration'] }} min
                                </p>
                            @endif
                            <div class="fs-3 my-3">{{ number_format($ticket->price, 2) }} zł</div>
                        </div>

                        <div id="payment-form" class="mt-4">
                            <div id="payment-message" class="alert alert-danger" style="display: none;"></div>

                            <div class="d-grid gap-2">
                                <button id="submit-button" class="btn btn-primary" type="button">
                                    <span id="button-text">Zapłać teraz</span>
                                    <span id="spinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                </button>
                                <a href="{{ route('tickets.cancel', ['session_id' => $sessionId]) }}" class="btn btn-outline-secondary">Anuluj</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stripe = Stripe('{{ config('services.stripe.key') }}');
            const submitButton = document.getElementById('submit-button');
            const spinner = document.getElementById('spinner');
            const buttonText = document.getElementById('button-text');

            submitButton.addEventListener('click', function() {
                submitButton.disabled = true;
                spinner.style.display = 'inline-block';
                buttonText.innerText = 'Przekierowywanie...';

                stripe.redirectToCheckout({
                    sessionId: '{{ $sessionId }}'
                }).then(function(result) {
                    if (result.error) {
                        const messageElement = document.getElementById('payment-message');
                        messageElement.textContent = result.error.message;
                        messageElement.style.display = 'block';

                        submitButton.disabled = false;
                        spinner.style.display = 'none';
                        buttonText.innerText = 'Zapłać teraz';
                    }
                });
            });
        });
    </script>
@endpush
