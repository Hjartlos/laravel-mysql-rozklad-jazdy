@extends('layouts.app')

@section('page_title', 'Dodaj zakupiony bilet (manualnie)')

@section('content')
    <div class="container">
        <div class="card auto-height-card">
            <div class="card-header">
                <h4>Dodaj nowy zakupiony bilet (manualnie)</h4>
                <p class="text-muted small">Uwaga: Manualne dodawanie zakupionych biletów powinno być używane tylko w wyjątkowych sytuacjach.</p>
            </div>
            <div class="card-body auto-height">
                <form action="{{ route('admin.purchasedtickets.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">Użytkownik <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id" required>
                                <option value="">Wybierz użytkownika</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" {{ old('user_id') == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór użytkownika jest wymagany.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket_id" class="form-label">Typ biletu <span class="text-danger">*</span></label>
                            <select class="form-select @error('ticket_id') is-invalid @enderror" name="ticket_id" id="ticket_id" required>
                                <option value="">Wybierz typ biletu</option>
                                @foreach($tickets as $ticket)
                                    <option value="{{ $ticket->ticket_id }}"
                                            data-hours="{{ $ticket->validity_hours }}"
                                        {{ old('ticket_id') == $ticket->ticket_id ? 'selected' : '' }}>
                                        {{ $ticket->ticket_name }} ({{ number_format($ticket->price, 2) }} zł, {{ $ticket->validity_hours }}h)
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór typu biletu jest wymagany.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">ID Powiązanej Transakcji (z tabeli Transakcji) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('transaction_id') is-invalid @enderror" id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" required placeholder="Wpisz ID istniejącej transakcji" step="1">
                        <div class="invalid-feedback">ID transakcji jest wymagane i musi być liczbą całkowitą.</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="valid_from" class="form-label">Ważny od <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror" id="valid_from" name="valid_from" value="{{ old('valid_from', now()->format('Y-m-d\TH:i')) }}" required>
                            <div class="invalid-feedback">Data "Ważny od" jest wymagana.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="valid_until" class="form-label">Ważny do <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until" name="valid_until" value="{{ old('valid_until') }}" readonly required>
                            <div class="invalid-feedback">Data "Ważny do" jest wymagana i nie może być wcześniejsza niż "Ważny od".</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktywny</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Oczekujący</option>
                            <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>Anulowany</option>
                        </select>
                        <div class="invalid-feedback">Status jest wymagany.</div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Dodaj zakupiony bilet</button>
                        <a href="{{ route('dashboard', ['tab' => 'purchasedtickets']) }}" class="btn btn-secondary">Anuluj</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ticketSelect = document.getElementById('ticket_id');
            const validFromInput = document.getElementById('valid_from');
            const validUntilInput = document.getElementById('valid_until');

            function calculateAndSetValidUntil() {
                const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
                const validityHours = selectedOption ? parseInt(selectedOption.dataset.hours) : 0;
                const validFromDateString = validFromInput.value;

                if (validityHours > 0 && validFromDateString) {
                    const validFromDate = new Date(validFromDateString);
                    const validUntilDate = new Date(validFromDate.getTime());
                    validUntilDate.setHours(validUntilDate.getHours() + validityHours);
                    const year = validUntilDate.getFullYear();
                    const month = String(validUntilDate.getMonth() + 1).padStart(2, '0');
                    const day = String(validUntilDate.getDate()).padStart(2, '0');
                    const hours = String(validUntilDate.getHours()).padStart(2, '0');
                    const minutes = String(validUntilDate.getMinutes()).padStart(2, '0');
                    validUntilInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                } else if (!validFromDateString || !ticketSelect.value) {
                    validUntilInput.value = '';
                }
            }
            calculateAndSetValidUntil();
            ticketSelect.addEventListener('change', calculateAndSetValidUntil);
            validFromInput.addEventListener('change', calculateAndSetValidUntil);
        });
    </script>
@endsection
