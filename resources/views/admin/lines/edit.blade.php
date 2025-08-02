@extends('layouts.app')

@section('page_title', 'Edycja linii: ' . $line->line_number)

@section('content')
    <div class="container">
        <h1>Edycja linii autobusowej</h1>
        <form action="{{ route('admin.lines.update', $line->line_id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="line_number" class="form-label">Numer linii</label>
                <input type="text" class="form-control @error('line_number') is-invalid @enderror" id="line_number" name="line_number" value="{{ old('line_number', $line->line_number) }}" required maxlength="10">
                @error('line_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Numer linii jest wymagany (max 10 znaków).</div>
                    @enderror
            </div>
            <div class="mb-3">
                <label for="line_name" class="form-label">Nazwa linii</label>
                <input type="text" class="form-control @error('line_name') is-invalid @enderror" id="line_name" name="line_name" value="{{ old('line_name', $line->line_name) }}" required maxlength="255">
                @error('line_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Nazwa linii jest wymagana.</div>
                    @enderror
            </div>
            <div class="mb-3">
                <label for="direction" class="form-label">Kierunek (opcjonalnie)</label>
                <input type="text" class="form-control @error('direction') is-invalid @enderror" id="direction" name="direction" value="{{ old('direction', $line->direction) }}" maxlength="255">
                @error('direction')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
            <div class="alert alert-danger d-none" id="stops_array_error_edit">Linia musi zawierać co najmniej 2 przystanki.</div>
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
                    @foreach($currentStopsData as $index => $stopOnLine)
                        <tr>
                            <td>{{ $stopOnLine->stop_name }}</td>
                            <td>{{ $stopOnLine->pivot->sequence }}</td>
                            <td>
                                <input type="hidden" name="stops[{{ $index }}][stop_id]" value="{{ $stopOnLine->stop_id }}">
                                <input type="hidden" name="stops[{{ $index }}][sequence]" value="{{ $stopOnLine->pivot->sequence }}">
                                <button type="button" class="btn btn-danger btn-sm remove-stop">Usuń</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <select class="form-select" id="stop_select">
                        <option value="">Wybierz przystanek do dodania</option>
                        @foreach($stops as $availableStop)
                            <option value="{{ $availableStop->stop_id }}">{{ $availableStop->stop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" id="add_stop_btn">Dodaj przystanek</button>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Zapisz zmiany</button>
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
            const stopsArrayErrorEdit = document.getElementById('stops_array_error_edit');
            const lineEditForm = document.querySelector('form.needs-validation');

            function validateStopsCountEdit() {
                const currentStopRows = stopsTableBody.querySelectorAll('tr').length;
                if (currentStopRows < 2) {
                    stopsArrayErrorEdit.classList.remove('d-none');
                    return false;
                }
                stopsArrayErrorEdit.classList.add('d-none');
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
                updateSequencesAndValidateEdit();
            });

            stopsTableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-stop')) {
                    if (confirm('Czy na pewno chcesz usunąć ten przystanek z trasy?')) {
                        e.target.closest('tr').remove();
                        updateSequencesAndValidateEdit();
                    }
                }
            });

            function updateSequencesAndValidateEdit() {
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
                validateStopsCountEdit();
            }

            if (stopsTableBody.querySelectorAll('tr').length > 0) {
                updateSequencesAndValidateEdit();
            } else {
                validateStopsCountEdit();
            }

            lineEditForm.addEventListener('submit', function(e) {
                updateSequencesAndValidateEdit();
                if (!validateStopsCountEdit()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    </script>
@endsection
