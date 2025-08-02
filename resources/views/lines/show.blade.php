@extends('layouts.app')

@section('page_title', 'Linia ' . $line->line_number . ': ' . $line->line_name)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card content-height">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="badge bg-primary me-2">{{ $line->line_number }}</span>
                                {{ $line->line_name }}
                            </h5>
                            <a href="{{ route('lines.index') }}" class="btn btn-outline-primary btn-sm">
                                Powrót
                            </a>
                        </div>
                    </div>
                    <div class="list-group list-group-flush scrollable">
                        @foreach($lineStops as $lineStop)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ $lineStop->stop->stop_name }}</h6>
                                            <div>
                                                <span class="badge bg-secondary me-2">{{ $lineStop->sequence }}</span>
                                                <button class="btn btn-sm btn-outline-primary toggle-timetable"
                                                        data-stop-id="{{ $lineStop->stop->stop_id }}">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            </div>
                                        </div>

                                        @if(isset($departureTimes[$lineStop->stop->stop_id]))
                                            <div class="timetable-container d-none" id="timetable-{{ $lineStop->stop->stop_id }}">
                                                <div class="nav nav-pills nav-fill mb-2" role="tablist">
                                                    <button type="button" class="nav-link active day-selector"
                                                            data-day-id="1" data-stop-id="{{ $lineStop->stop->stop_id }}">
                                                        Dni robocze
                                                    </button>
                                                    <button type="button" class="nav-link day-selector"
                                                            data-day-id="2" data-stop-id="{{ $lineStop->stop->stop_id }}">
                                                        Soboty
                                                    </button>
                                                    <button type="button" class="nav-link day-selector"
                                                            data-day-id="3" data-stop-id="{{ $lineStop->stop->stop_id }}">
                                                        Niedziele
                                                    </button>
                                                </div>

                                                @foreach([1, 2, 3] as $dayId)
                                                    <div class="departure-times {{ $dayId == 1 ? '' : 'd-none' }}"
                                                         id="times-{{ $lineStop->stop->stop_id }}-{{ $dayId }}">
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @if(isset($departureTimes[$lineStop->stop->stop_id][$dayId]))
                                                            @foreach($departureTimes[$lineStop->stop->stop_id][$dayId] as $time)
                                                                <span class="time-badge {{ isset($nextDeparturesByStop[$lineStop->stop->stop_id]) && $nextDeparturesByStop[$lineStop->stop->stop_id]->time_id == $time->time_id ? 'next-departure' : '' }}">
                                                                    {{ \Carbon\Carbon::parse($time->departure_time)->format('H:i') }}
                                                                </span>
                                                            @endforeach
                                                            @else
                                                                <span class="text-muted small">Brak odjazdów</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
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
            window.lineStops = @json($lineStopsForMap);
            window.currentView = 'line';

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.toggle-timetable').forEach(button => {
                    button.addEventListener('click', function() {
                        const stopId = this.dataset.stopId;
                        const timetableContainer = document.getElementById(`timetable-${stopId}`);

                        if (timetableContainer.classList.contains('d-none')) {
                            document.querySelectorAll('.timetable-container').forEach(container => {
                                container.classList.add('d-none');
                            });
                            timetableContainer.classList.remove('d-none');
                        } else {
                            timetableContainer.classList.add('d-none');
                        }
                    });
                });

                document.querySelectorAll('.day-selector').forEach(button => {
                    button.addEventListener('click', function() {
                        const stopId = this.dataset.stopId;
                        const dayId = this.dataset.dayId;

                        document.querySelectorAll(`[data-stop-id="${stopId}"].day-selector`).forEach(btn => {
                            btn.classList.remove('active');
                        });
                        this.classList.add('active');

                        document.querySelectorAll(`[id^="times-${stopId}-"]`).forEach(timeContainer => {
                            timeContainer.classList.add('d-none');
                        });
                        document.getElementById(`times-${stopId}-${dayId}`).classList.remove('d-none');
                    });
                });
            });

        </script>
        <script src="{{ asset('js/map.js') }}"></script>
    @endpush
@endsection

