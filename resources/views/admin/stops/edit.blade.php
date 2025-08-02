@extends('layouts.app')

@section('page_title', 'Edycja przystanku: ' . $stop->stop_name)

@section('content')
    <div class="container">
        <h1>Edytuj przystanek</h1>
        <form action="{{ route('admin.stops.update', $stop->stop_id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="stop_name" class="form-label">Nazwa przystanku</label>
                        <input type="text" class="form-control" id="stop_name" name="stop_name" value="{{ old('stop_name', $stop->stop_name) }}" required maxlength="255">
                        <div class="invalid-feedback">Nazwa przystanku jest wymagana.</div>
                    </div>
                    <div class="mb-3">
                        <label for="location_lat" class="form-label">Szerokość geograficzna</label>
                        <input type="number" step="any" class="form-control" id="location_lat" name="location_lat" value="{{ old('location_lat', $stop->location_lat) }}" required>
                        <div class="invalid-feedback">Szerokość geograficzna jest wymagana i musi być liczbą.</div>
                    </div>
                    <div class="mb-3">
                        <label for="location_lon" class="form-label">Długość geograficzna</label>
                        <input type="number" step="any" class="form-control" id="location_lon" name="location_lon" value="{{ old('location_lon', $stop->location_lon) }}" required>
                        <div class="invalid-feedback">Długość geograficzna jest wymagana i musi być liczbą.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Wybierz lokalizację na mapie</label>
                    <div id="map" data-view-context="stop-form" style="height: 300px; width: 100%; border-radius: 0.25rem; border: 1px solid #ced4da;"></div>
                    <small class="form-text text-muted">Kliknij na mapę, aby ustawić współrzędne.</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Zapisz zmiany</button>
            <a href="{{ route('dashboard', ['tab' => 'stops']) }}" class="btn btn-secondary mt-3">Anuluj</a>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/map.js') }}"></script>
@endpush
