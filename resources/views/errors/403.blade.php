@extends('layouts.app')

@section('page_title', 'Brak dostępu (403)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-danger">403</h1>
                        <h2 class="mb-4">Brak dostępu</h2>
                        <p class="lead text-muted mb-4">
                            Przepraszamy, ale nie masz uprawnień do wyświetlenia tej strony.
                        </p>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Wróć na stronę główną
                        </a>
                        @if (Auth::check() && !Auth::user()->isAdmin())
                            <p class="mt-3 small text-muted">
                                Jeśli uważasz, że to błąd, skontaktuj się z administratorem.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
