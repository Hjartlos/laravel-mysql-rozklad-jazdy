@extends('layouts.app')

@section('page_title', 'Niedozwolona metoda (405)')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="display-1 fw-bold text-warning">405</h1>
                        <h2 class="mb-4">Niedozwolona metoda</h2>
                        <p class="lead text-muted mb-4">
                            Użyta metoda HTTP nie jest dozwolona dla tego zasobu.
                        </p>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Wróć
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Strona główna
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
