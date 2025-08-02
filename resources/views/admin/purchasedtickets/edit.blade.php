@extends('layouts.app')

@section('page_title', 'Edycja zakupionego biletu: #' . $purchasedTicket->purchase_id)

@section('content')
    <div class="container">
        <div class="card auto-height-card">
            <div class="card-header">
                <h4>Edycja zakupionego biletu: #{{ $purchasedTicket->purchase_id }}</h4>
            </div>
            <div class="card-body auto-height">
                <form action="{{ route('admin.purchasedtickets.update', $purchasedTicket->purchase_id) }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">Użytkownik <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" {{ old('user_id', $purchasedTicket->user_id) == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór użytkownika jest wymagany.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket_id" class="form-label">Typ biletu <span class="text-danger">*</span></label>
                            <select class="form-select @error('ticket_id') is-invalid @enderror" name="ticket_id" id="ticket_id" required>
                                @foreach($tickets as $ticket)
                                    <option value="{{ $ticket->ticket_id }}" {{ old('ticket_id', $purchasedTicket->ticket_id) == $ticket->ticket_id ? 'selected' : '' }}>
                                        {{ $ticket->ticket_name }} ({{ number_format($ticket->price, 2) }} zł, {{ $ticket->validity_hours }}h)
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Wybór typu biletu jest wymagany.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id_display" class="form-label">ID Transakcji</label>
                        <input type="text" class="form-control" id="transaction_id_display" value="{{ $purchasedTicket->transaction_id }}" readonly disabled>
                        <small class="text-muted">ID transakcji jest powiązane z systemem płatności i nie powinno być edytowane.</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="valid_from" class="form-label">Ważny od <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror" id="valid_from" name="valid_from" value="{{ old('valid_from', $purchasedTicket->valid_from->format('Y-m-d\TH:i')) }}" required>
                            <div class="invalid-feedback">Data "Ważny od" jest wymagana.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="valid_until" class="form-label">Ważny do <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until" name="valid_until" value="{{ old('valid_until', $purchasedTicket->valid_until->format('Y-m-d\TH:i')) }}" required>
                            <div class="invalid-feedback">Data "Ważny do" jest wymagana i nie może być wcześniejsza niż "Ważny od".</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                            <option value="aktywny" {{ old('status', $purchasedTicket->status) == 'aktywny' ? 'selected' : '' }}>Aktywny</option>
                            <option value="oczekujący" {{ old('status', $purchasedTicket->status) == 'oczekujący' ? 'selected' : '' }}>Oczekujący</option>
                            <option value="anulowany" {{ old('status', $purchasedTicket->status) == 'anulowany' ? 'selected' : '' }}>Anulowany</option>
                            <option value="wygasły" {{ old('status', $purchasedTicket->status) == 'wygasły' ? 'selected' : '' }}>Wygasły</option>
                        </select>
                        <div class="invalid-feedback">
                            @error('status')
                            {{ $message }}
                            @else
                                Status jest wymagany.
                                @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Zapisz zmiany</button>
                        <a href="{{ route('dashboard', ['tab' => 'purchasedtickets']) }}" class="btn btn-secondary">Anuluj</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
