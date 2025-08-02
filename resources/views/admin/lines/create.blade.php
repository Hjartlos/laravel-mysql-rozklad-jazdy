@extends('layouts.app')

@section('page_title', 'Dodaj nową linię')

@section('content')
    <div class="container">
        <h1>Dodaj nową linię autobusową</h1>
        <form action="{{ route('admin.lines.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="mb-3">
                <label for="line_number" class="form-label">Numer linii</label>
                <input type="text" class="form-control @error('line_number') is-invalid @enderror" id="line_number" name="line_number" value="{{ old('line_number') }}" required maxlength="10">
                @error('line_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Numer linii jest wymagany (max 10 znaków).</div>
                    @enderror
            </div>
            <div class="mb-3">
                <label for="line_name" class="form-label">Nazwa linii</label>
                <input type="text" class="form-control @error('line_name') is-invalid @enderror" id="line_name" name="line_name" value="{{ old('line_name') }}" required maxlength="255">
                @error('line_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Nazwa linii jest wymagana.</div>
                    @enderror
            </div>
            <div class="mb-3">
                <label for="direction" class="form-label">Kierunek (opcjonalnie)</label>
                <input type="text" class="form-control @error('direction') is-invalid @enderror" id="direction" name="direction" value="{{ old('direction') }}" maxlength="255">
                @error('direction')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Np. "Zajezdnia - Rynek" lub "Pętla"</small>
            </div>

            <h3 class="mt-4">Przystanki na trasie</h3>
            @error('stops')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('stops.*.stop_id')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('stops.*.sequence')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="alert alert-danger d-none" id="stops_array_error">Linia musi zawierać co najmniej 2 przystanki.</div>
            <div class="table-responsive">
                <table class="table table-bordered" id="stops_table">
                    <thead>
                    <tr>
                        <th>Przystanek</th>
                        <th>Kolejność</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($oldStopsData))
                        @foreach($oldStopsData as $index => $stopData)
                            <tr>
                                <td>{{ $stopData['stop_name'] }}</td>
                                <td>{{ $stopData['sequence'] }}</td>
                                <td>
                                    <input type="hidden" name="stops[{{ $index }}][stop_id]" value="{{ $stopData['stop_id'] }}">
                                    <input type="hidden" name="stops[{{ $index }}][sequence]" value="{{ $stopData['sequence'] }}">
                                    <button type="button" class="btn btn-danger btn-sm remove-stop">Usuń</button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <select class="form-select" id="stop_select">
                        <option value="">Wybierz przystanek</option>
                        @foreach($stops as $stop)
                            <option value="{{ $stop->stop_id }}">{{ $stop->stop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" id="add_stop_btn">Dodaj przystanek</button>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Zapisz linię</button>
                <a href="{{ route('dashboard', ['tab' => 'lines']) }}" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stopSelect = document.getElementById('stop_select');
            const addStopBtn = document.getElementById('add_stop_btn');
            const stopsTableBody = document.getElementById('stops_table').querySelector('tbody');
            let stopClientIndexCounter = stopsTableBody.querySelectorAll('tr').length;
            const stopsArrayError = document.getElementById('stops_array_error');
            const lineForm = document.querySelector('form.needs-validation');

            function validateStopsCount() {
                const currentStopRows = stopsTableBody.querySelectorAll('tr').length;
                if (currentStopRows < 2) {
                    stopsArrayError.classList.remove('d-none');
                    return false;
                }
                stopsArrayError.classList.add('d-none');
                return true;
            }

            addStopBtn.addEventListener('click', function() {
                const stopId = stopSelect.value;
                if (!stopId) {
                    alert('Wybierz przystanek z listy!');
                    return;
                }
                const stopName = stopSelect.options[stopSelect.selectedIndex].textContent;

                const existingStops = stopsTableBody.querySelectorAll(`input[name^="stops["][name$="[stop_id]"][value="${stopId}"]`);
                if (existingStops.length > 0) {
                    alert('Ten przystanek został już dodany do trasy!');
                    return;
                }

                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${stopName}</td>
                    <td></td>
                    <td>
                        <input type="hidden" name="stops[${stopClientIndexCounter}][stop_id]" value="${stopId}">
                        <input type="hidden" name="stops[${stopClientIndexCounter}][sequence]" value="">
                        <button type="button" class="btn btn-danger btn-sm remove-stop">Usuń</button>
                    </td>
                `;
                stopsTableBody.appendChild(newRow);
                stopClientIndexCounter++;
                stopSelect.value = '';
                updateSequencesAndValidate();
            });

            stopsTableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-stop')) {
                    e.target.closest('tr').remove();
                    updateSequencesAndValidate();
                }
            });

            function updateSequencesAndValidate() {
                const rows = stopsTableBody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    const newSequence = index + 1;
                    row.querySelector('td:nth-child(2)').textContent = newSequence;

                    const stopIdInput = row.querySelector('input[name$="[stop_id]"]');
                    const sequenceInput = row.querySelector('input[name$="[sequence]"]');

                    if (stopIdInput) stopIdInput.name = `stops[${index}][stop_id]`;
                    if (sequenceInput) {
                        sequenceInput.name = `stops[${index}][sequence]`;
                        sequenceInput.value = newSequence;
                    }
                });
                validateStopsCount();
            }

            if (stopsTableBody.querySelectorAll('tr').length > 0) {
                updateSequencesAndValidate();
            } else {
                validateStopsCount();
            }

            lineForm.addEventListener('submit', function(e) {
                updateSequencesAndValidate();
                if (!validateStopsCount()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    </script>
@endsection
