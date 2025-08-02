@extends('layouts.app')

@section('page_title', 'Przystanek: ' . $stop->stop_name)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $stop->stop_name }}</h5>
                            <a href="{{ route('stops.index') }}" class="btn btn-outline-primary btn-sm">
                                Powrót
                            </a>
                        </div>
                    </div>
                    <div class="scrollable">
                        <div class="list-group list-group-flush">
                            @foreach($stopLines as $line)
                                <a href="{{ route('stops.show', ['stopId' => $stop->stop_id, 'line_id' => $line['line_id']]) }}"
                                   class="list-group-item list-group-item-action {{ request('line_id') == $line['line_id'] ? 'active' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ request('line_id') == $line['line_id'] ? 'bg-white text-primary' : 'bg-primary' }} me-2">
                                            {{ $line['line_number'] }}
                                        </span>
                                        <span>{{ $line['line_name'] }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        @if($selectedLine)
                            <div class="border-top">
                                <div class="p-3 bg-light border-bottom">
                                    <h6 class="mb-0">Trasa linii {{ $selectedLine->line_number }}</h6>
                                </div>

                                <div class="timetable-container p-3">
                                    <div class="nav nav-pills mb-3" role="tablist">
                                        @foreach([1 => 'Dni robocze', 2 => 'Soboty', 3 => 'Niedziele'] as $dayId => $dayName)
                                            <button class="nav-link {{ $dayId === $activeTabId ? 'active' : '' }}"
                                                    data-bs-toggle="pill"
                                                    data-bs-target="#day-{{ $dayId }}"
                                                    type="button"
                                                    role="tab">
                                                {{ $dayName }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="tab-content">
                                        @foreach([1 => 'Dni robocze', 2 => 'Soboty', 3 => 'Niedziele'] as $dayId => $dayName)
                                            <div class="tab-pane fade {{ $dayId === $activeTabId ? 'show active' : '' }}"
                                                 id="day-{{ $dayId }}">
                                                @if(isset($departureTimes[$dayId]))
                                                    <div class="times-grid">
                                                        @foreach($departureTimes[$dayId] as $time)
                                                            <span class="time-badge {{ $nextDeparture && $time->time_id === $nextDeparture->time_id ? 'next-departure' : '' }}">
                                                                {{ \Carbon\Carbon::parse($time->departure_time)->format('H:i') }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted text-center py-3">
                                                        Brak odjazdów
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="stops-list">
                                    @foreach($lineStops as $lineStop)
                                        <div class="stop-item {{ $lineStop['is_current'] ? 'current' : '' }}">
                                            <div class="stop-content">
                                                <div class="stop-marker"></div>
                                                <span class="stop-name">{{ $lineStop['stop_name'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
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

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
        <script>
            window.currentStop = {!! json_encode([
                'stop_id' => $stop->stop_id,
                'stop_name' => $stop->stop_name,
                'location_lat' => $stop->location_lat,
                'location_lon' => $stop->location_lon
            ]) !!};
            window.lineStops = @json($lineStops ?? null);
        </script>
        <script src="{{ asset('js/map.js') }}"></script>
    @endpush
@endsection
