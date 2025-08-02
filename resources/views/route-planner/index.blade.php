@extends('layouts.app')

@section('page_title', 'Planowanie trasy')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card content-height">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Planowanie trasy</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('route-planner.search') }}" id="routeForm" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="fromInput" class="form-label">Przystanek początkowy</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control @error('from') is-invalid @enderror"
                                           id="fromInput" placeholder="Wpisz nazwę przystanku" autocomplete="off" required>
                                    <input type="hidden" id="from" name="from" value="{{ old('from', request('from')) }}" required>
                                    <div id="fromDropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;">
                                        @foreach($stops as $stop)
                                            <div class="dropdown-item from-stop-item"
                                                 data-stop-id="{{ $stop->stop_id }}"
                                                 data-stop-name="{{ $stop->stop_name }}">
                                                {{ $stop->stop_name }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="invalid-feedback">Wybierz przystanek początkowy.</div>
                                </div>
                                @error('from')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="toInput" class="form-label">Przystanek końcowy</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control @error('to') is-invalid @enderror"
                                           id="toInput" placeholder="Wpisz nazwę przystanku" autocomplete="off" required>
                                    <input type="hidden" id="to" name="to" value="{{ old('to', request('to')) }}" required>
                                    <div id="toDropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;">
                                        @foreach($stops as $stop)
                                            <div class="dropdown-item to-stop-item"
                                                 data-stop-id="{{ $stop->stop_id }}"
                                                 data-stop-name="{{ $stop->stop_name }}">
                                                {{ $stop->stop_name }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="invalid-feedback">Wybierz przystanek końcowy.</div>
                                </div>
                                @error('to')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="departure_time" class="form-label">Czas odjazdu</label>
                                <input type="datetime-local"
                                       class="form-control @error('departure_time') is-invalid @enderror"
                                       id="departure_time" name="departure_time"
                                       value="{{ old('departure_time', request('departure_time', now()->format('Y-m-d\TH:i'))) }}"
                                       required>
                                <div class="invalid-feedback">Wybierz czas odjazdu.</div>
                                @error('departure_time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Wyszukaj połączenie</button>
                                <button type="button" id="resetButton" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </form>
                        @if(isset($routes))
                            <div class="mt-3" style="height: calc(100vh - 400px); overflow-y: auto;">
                                <h6 class="mb-1 small">Znalezione połączenia:</h6>
                                @forelse($routes as $index => $route)
                                    <div class="card mb-1" style="cursor: pointer;" onclick="window.transportMap.selectRoute({{ $index }})">
                                        <div class="card-body p-0" style="padding: 4px 6px !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <a href="{{ route('lines.show', $route['line_id']) }}"
                                                       class="text-decoration-none"
                                                       onclick="event.stopPropagation();">
                                                        <span class="badge bg-primary">{{ $route['line_number'] }}</span>
                                                        <span class="ms-1 small">{{ $route['line_name'] }}</span>
                                                    </a>
                                                    @if(isset($route['next_day']) && $route['next_day'])
                                                        <span class="badge bg-warning ms-1">Następny dzień</span>
                                                    @endif
                                                </div>
                                                <span class="badge bg-secondary">{{ $route['duration'] }} min</span>
                                            </div>
                                            <div class="route-summary">
                                                <div class="d-flex text-success">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <span>{{ $route['from_stop'] }} ({{ $route['all_stops'][0]['sequence'] }}) {{ $route['departure_time'] }}</span>
                                                </div>
                                                <div class="d-flex text-muted">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                    <span class="ms-1">{{ count($route['all_stops']) - 2 }} przystanków</span>
                                                </div>
                                                <div class="d-flex text-danger">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    @php
                                                        $lastStop = $route['all_stops'][count($route['all_stops']) - 1];
                                                    @endphp
                                                    <span>{{ $route['to_stop'] }} ({{ $lastStop['sequence'] }}) {{ $route['arrival_time'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info py-1 small">
                                        Nie znaleziono połączeń dla wybranych przystanków.
                                    </div>
                                @endforelse
                            </div>
                        @endif
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
@endsection
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="{{ asset('js/combobox.js') }}"></script>
    <script>
        window.currentView = 'route-planner';
        window.stops = @json($stops);
        window.routes = @json($routes ?? null);
        document.addEventListener('DOMContentLoaded', function() {
            initCombobox('fromInput', 'from', 'fromDropdown', '.from-stop-item');
            initCombobox('toInput', 'to', 'toDropdown', '.to-stop-item');
            document.getElementById('resetButton').addEventListener('click', function() {
                document.getElementById('fromInput').value = '';
                document.getElementById('from').value = '';
                document.getElementById('toInput').value = '';
                document.getElementById('to').value = '';
                const now = new Date();
                document.getElementById('departure_time').value = now.getFullYear() + '-' +
                    String(now.getMonth() + 1).padStart(2, '0') + '-' +
                    String(now.getDate()).padStart(2, '0') + 'T' +
                    String(now.getHours()).padStart(2, '0') + ':' +
                    String(now.getMinutes()).padStart(2, '0');
                localStorage.removeItem('lastDepartureTime');
                const routesContainer = document.querySelector('.mt-3[style*="overflow-y: auto"]');
                if (routesContainer) routesContainer.parentNode.removeChild(routesContainer);
                if (window.transportMap) {
                    window.transportMap.clearMarkers();
                    window.transportMap.showAllStops();
                }
                window.history.pushState({}, '', '{{ route("route-planner") }}');
                window.routes = null;
                document.getElementById('routeForm').classList.remove('was-validated');
            });
            document.getElementById('routeForm').addEventListener('submit', function() {
                const departureTime = document.getElementById('departure_time').value;
                localStorage.setItem('lastDepartureTime', departureTime);
            });
            setInitialValues();
            function setInitialValues() {
                const fromValue = document.getElementById('from').value;
                if (fromValue) {
                    for (const stop of window.stops) {
                        if (stop.stop_id.toString() === fromValue) {
                            document.getElementById('fromInput').value = stop.stop_name;
                            break;
                        }
                    }
                }
                const toValue = document.getElementById('to').value;
                if (toValue) {
                    for (const stop of window.stops) {
                        if (stop.stop_id.toString() === toValue) {
                            document.getElementById('toInput').value = stop.stop_name;
                            break;
                        }
                    }
                }
                const savedDepartureTime = localStorage.getItem('lastDepartureTime');
                if (savedDepartureTime && document.getElementById('departure_time').value === "{{ now()->format('Y-m-d\TH:i') }}") {
                    document.getElementById('departure_time').value = savedDepartureTime;
                }
            }
        });
    </script>
    <script src="{{ asset('js/map.js') }}"></script>
@endpush
