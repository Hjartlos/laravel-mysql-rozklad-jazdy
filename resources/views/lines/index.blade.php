@extends('layouts.app')

@section('page_title', 'Rozkład linii')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Linie komunikacyjne</h5>
                    </div>
                    <div class="list-group list-group-flush scrollable">
                        @foreach($lines as $line)
                            <a href="{{ route('lines.show', $line->line_id) }}"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary me-2">{{ $line->line_number }}</span>
                                        {{ $line->line_name }}
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div id="map" class="content-height"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
        <script>
            window.currentView = 'lines';
            window.stops = @json($stops ?? null);
        </script>
        <script src="{{ asset('js/map.js') }}"></script>
    @endpush

@endsection
