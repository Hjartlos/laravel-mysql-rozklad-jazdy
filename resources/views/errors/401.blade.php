@extends('layouts.app')

@section('page_title', 'Brak autoryzacji (401)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-warning">401</h1>
                        <h2 class="mb-4">Brak autoryzacji</h2>
                        <p class="lead text-muted mb-4">
                            Przepraszamy, ale musisz być zalogowany, aby uzyskać dostęp do tej strony.
                        </p>
                        <a href="{{ route('login') }}" class="btn btn-primary me-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Zaloguj się
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            Wróć na stronę główną
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
