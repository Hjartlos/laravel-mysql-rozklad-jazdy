@extends('layouts.app')

@section('page_title', 'Kup bilet')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h2>Kup bilet</h2>
                <p class="text-muted">Wybierz rodzaj biletu lub zaplanuj trasę</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card auto-height-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Zaplanuj trasę i kup bilet</h5>
                    </div>
                    <div class="card-body auto-height">
                        <p>Wybierz przystanki początkowy i końcowy, linię oraz typ biletu, aby wyliczyć cenę dla konkretnej trasy.</p>
                        <a href="{{ route('tickets.select-route') }}" class="btn btn-primary">
                            <i class="fas fa-map-marked-alt"></i> Wybierz trasę i kup bilet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
