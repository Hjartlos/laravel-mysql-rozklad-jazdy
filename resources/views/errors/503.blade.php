@extends('layouts.app')

@section('page_title', 'Usługa niedostępna (503)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-warning">503</h1>
                        <h2 class="mb-4">Usługa tymczasowo niedostępna</h2>
                        <p class="lead text-muted mb-4">
                            Serwer jest obecnie przeciążony lub trwają prace konserwacyjne. Prosimy spróbować ponownie za chwilę.
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
