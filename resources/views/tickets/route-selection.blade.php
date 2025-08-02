@extends('layouts.app')

@section('page_title', 'Wybierz trasę dla biletu')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Zakup biletu</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tickets.calculate') }}" id="ticketForm" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="fromInput" class="form-label">Przystanek początkowy</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control @error('from_stop_id') is-invalid @enderror"
                                           id="fromInput" placeholder="Wpisz nazwę przystanku" autocomplete="off" required>
                                    <input type="hidden" id="from_stop_id" name="from_stop_id" value="{{ old('from_stop_id') }}" required>
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
                                @error('from_stop_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="toInput" class="form-label">Przystanek końcowy</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control @error('to_stop_id') is-invalid @enderror"
                                           id="toInput" placeholder="Wpisz nazwę przystanku" autocomplete="off" required>
                                    <input type="hidden" id="to_stop_id" name="to_stop_id" value="{{ old('to_stop_id') }}" required>
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
                                @error('to_stop_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="line_id" class="form-label">Linia</label>
                                <select class="form-select @error('line_id') is-invalid @enderror" id="line_id" name="line_id" disabled required>
                                    <option value="">Najpierw wybierz przystanki</option>
                                </select>
                                <div class="invalid-feedback">Wybierz linię.</div>
                                @error('line_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="ticket_id" class="form-label">Rodzaj biletu</label>
                                <select class="form-select @error('ticket_id') is-invalid @enderror" id="ticket_id" name="ticket_id" required>
                                    <option value="">Wybierz rodzaj biletu</option>
                                    @foreach($tickets as $ticket)
                                        <option value="{{ $ticket->ticket_id }}">{{ $ticket->ticket_name }} ({{ $ticket->validity_hours }}h)</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Wybierz rodzaj biletu.</div>
                                @error('ticket_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Oblicz cenę i kup bilet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
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
        window.currentView = 'ticket-purchase';
        window.stops = @json($stops);
        document.addEventListener('DOMContentLoaded', function() {
            initCombobox('fromInput', 'from_stop_id', 'fromDropdown', '.from-stop-item');
            initCombobox('toInput', 'to_stop_id', 'toDropdown', '.to-stop-item');
            const fromStopInput = document.getElementById('from_stop_id');
            const toStopInput = document.getElementById('to_stop_id');
            const lineSelect = document.getElementById('line_id');
            [fromStopInput, toStopInput].forEach(input => {
                input.addEventListener('change', function() {
                    updateAvailableLines();
                });
            });
            window.handleStopMarkerClick = function(stop) {
                if (!fromStopInput.value) {
                    document.getElementById('fromInput').value = stop.stop_name;
                    fromStopInput.value = stop.stop_id;
                    fromStopInput.dispatchEvent(new Event('change'));
                } else if (!toStopInput.value) {
                    document.getElementById('toInput').value = stop.stop_name;
                    toStopInput.value = stop.stop_id;
                    toStopInput.dispatchEvent(new Event('change'));
                } else {
                    document.getElementById('toInput').value = stop.stop_name;
                    toStopInput.value = stop.stop_id;
                    toStopInput.dispatchEvent(new Event('change'));
                }
            };
            function updateAvailableLines() {
                const fromStopId = fromStopInput.value;
                const toStopId = toStopInput.value;
                if (!fromStopId || !toStopId) {
                    lineSelect.innerHTML = '<option value="">Najpierw wybierz przystanki</option>';
                    lineSelect.disabled = true;
                    return;
                }
                const url = `/api/common-lines?from_stop_id=${fromStopId}&to_stop_id=${toStopId}`;
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        lineSelect.innerHTML = '';
                        if (data.lines.length === 0) {
                            lineSelect.innerHTML = '<option value="">Brak dostępnych linii</option>';
                            lineSelect.disabled = true;
                        } else {
                            lineSelect.disabled = false;
                            lineSelect.innerHTML += '<option value="">Wybierz linię</option>';
                            data.lines.forEach(line => {
                                lineSelect.innerHTML += `<option value="${line.line_id}">Linia ${line.line_number}: ${line.line_name}</option>`;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Szczegóły błędu pobierania linii:', error);
                        lineSelect.innerHTML = '<option value="">Błąd pobierania linii</option>';
                        lineSelect.disabled = true;
                    });
            }
        });
    </script>
    <script src="{{ asset('js/map.js') }}"></script>
@endpush
