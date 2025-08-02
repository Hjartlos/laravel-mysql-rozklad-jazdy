@extends('layouts.app')

@section('page_title', 'Edycja rozkładu dla linii ' . $trip->line->line_number)

@section('content')
    <div class="container">
        <div class="card auto-height-card">
            <div class="card-header">
                <h4>Edycja rozkładu jazdy</h4>
            </div>
            <div class="card-body auto-height">
                <form action="{{ route('admin.timetable.update', $trip) }}" method="POST" id="timetableForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="line_id" class="form-label">Linia</label>
                            <select class="form-select" name="line_id" id="line_id" required>
                                @foreach($lines as $line)
                                    <option value="{{ $line->line_id }}" {{ old('line_id', $trip->line_id) == $line->line_id ? 'selected' : '' }}>
                                        {{ $line->line_number }} ({{ $line->line_name }} - {{ $line->direction ?? 'brak kierunku' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór linii jest wymagany.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="day_id" class="form-label">Dzień kursowania</label>
                            <select class="form-select" name="day_id" id="day_id" required>
                                @foreach($operatingDays as $day)
                                    <option value="{{ $day->day_id }}" {{ old('day_id', $trip->day_id) == $day->day_id ? 'selected' : '' }}>
                                        {{ $day->name }} {{ $day->description ? '('.$day->description.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór dnia kursowania jest wymagany.</div>
                        </div>
                    </div>

                    <div class="row mb-3 border-top pt-3">
                        <div class="col-md-12">
                            <h5>Dodaj przystanek i godzinę odjazdu</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="stop_select" class="form-label">Przystanek</label>
                                    <select class="form-select" id="stop_select">
                                        <option value="">Ładowanie przystanków...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="departure_time" class="form-label">Czas odjazdu</label>
                                    <input type="time" class="form-control" id="departure_time">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="addStopBtn">Dodaj</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger" id="no-stops-alert" style="display: none;">
                                Musisz dodać co najmniej jeden przystanek z czasem odjazdu.
                            </div>
                            <div id="stops-container" class="mb-3">
                                <h6><span id="stops-counter">{{ old('times') ? count(old('times')) : count($departureTimes) }} przystanków</span></h6>
                                <div id="stops-list" class="mt-2">
                                </div>
                                <div id="pagination-container" class="d-flex justify-content-center mt-3" style="display: none !important;">
                                    <nav>
                                        <ul class="pagination pagination-sm" id="pagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success" id="saveBtn">Zapisz zmiany</button>
                        <a href="{{ route('dashboard', ['tab' => 'timetable']) }}" class="btn btn-secondary">Anuluj</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        window.isEditMode = true;
        window.initialStopCount = {{ old('times') ? count(old('times')) : count($departureTimes) }};
        @php
            $existingStopsData = old('times', $departureTimes->map(function ($time) {
                return [
                    'time_id' => $time->time_id,
                    'stop_id' => $time->stop_id,
                    'stop_name' => $time->stop->stop_name,
                    'departure_time' => substr($time->departure_time, 0, 5)
                ];
            }));
        @endphp
            window.existingStops = @json($existingStopsData);
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/timetable.js') }}"></script>
@endpush
