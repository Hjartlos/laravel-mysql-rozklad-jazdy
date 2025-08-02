@extends('layouts.app')

@section('page_title', 'Dodaj nowy rozkład jazdy')

@section('content')
    <div class="container">
        <div class="card auto-height-card">
            <div class="card-header">
                <h4>Dodaj nowy rozkład jazdy</h4>
            </div>
            <div class="card-body auto-height">
                <form action="{{ route('admin.timetable.store') }}" method="POST" id="timetableForm" class="needs-validation" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="line_id" class="form-label">Linia</label>
                            <select class="form-select" name="line_id" id="line_id" required>
                                <option value="">Wybierz linię</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->line_id }}" {{ old('line_id') == $line->line_id ? 'selected' : ''}}>
                                        {{ $line->line_number }} ({{ $line->line_name }} - {{ $line->direction ?? 'brak kierunku' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór linii jest wymagany.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="day_id" class="form-label">Dzień kursowania</label>
                            <select class="form-select" name="day_id" id="day_id" required>
                                <option value="">Wybierz dzień</option>
                                @foreach($operatingDays as $day)
                                    <option value="{{ $day->day_id }}" {{ old('day_id') == $day->day_id ? 'selected' : ''}}>
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
                                    <select class="form-select" id="stop_select" disabled>
                                        <option value="">Najpierw wybierz linię</option>
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
                                <h6><span id="stops-counter">0 przystanków</span></h6>
                                <div id="stops-list" class="mt-2"></div>
                                <div id="pagination-container" class="d-flex justify-content-center mt-3" style="display: none !important;">
                                    <nav>
                                        <ul class="pagination pagination-sm" id="pagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success" id="saveBtn" disabled>Zapisz rozkład</button>
                        <a href="{{ route('dashboard', ['tab' => 'timetable']) }}" class="btn btn-secondary">Anuluj</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        window.isEditMode = false;
        window.initialStopCount = 0;
        window.existingStops = @json(old('times', []));
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/timetable.js') }}"></script>
@endpush
