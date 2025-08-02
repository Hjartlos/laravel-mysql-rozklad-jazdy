@extends('layouts.app')

@section('page_title', 'Błąd serwera (500)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-danger">500</h1>
                        <h2 class="mb-4">Wewnętrzny błąd serwera</h2>
                        <p class="lead text-muted mb-4">
                            Przepraszamy, wystąpił nieoczekiwany błąd po naszej stronie. Pracujemy nad jego rozwiązaniem. Prosimy spróbować ponownie później.
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
