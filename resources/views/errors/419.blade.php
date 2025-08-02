@extends('layouts.app')

@section('page_title', 'Strona wygasła (419)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-info">419</h1>
                        <h2 class="mb-4">Strona wygasła</h2>
                        <p class="lead text-muted mb-4">
                            Twoja sesja wygasła lub strona była zbyt długo nieaktywna. Odśwież stronę i spróbuj ponownie.
                        </p>
                        <button onclick="window.location.reload()" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i>Odśwież stronę
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2">
                            Wróć na stronę główną
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
