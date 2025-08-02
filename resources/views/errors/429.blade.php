@extends('layouts.app')

@section('page_title', 'Zbyt wiele żądań (429)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-warning">429</h1>
                        <h2 class="mb-4">Zbyt wiele żądań</h2>
                        <p class="lead text-muted mb-4">
                            Wysłałeś zbyt wiele żądań w krótkim czasie. Prosimy spróbować ponownie za chwilę.
                        </p>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Wróć na stronę główną
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
