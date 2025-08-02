@extends('layouts.app')

@section('page_title', 'Edycja biletu: ' . $ticket->ticket_name)

@section('content')
    <div class="container">
        <h1>Edytuj bilet: {{ $ticket->ticket_name }}</h1>
        <form action="{{ route('admin.tickets.update', $ticket->ticket_id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="ticket_name" class="form-label">Nazwa biletu</label>
                <input type="text" class="form-control" id="ticket_name" name="ticket_name" value="{{ old('ticket_name', $ticket->ticket_name) }}" required maxlength="255">
                <div class="invalid-feedback">Nazwa biletu jest wymagana.</div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Opis</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $ticket->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Cena (zł)</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price', $ticket->price) }}" required min="0">
                <div class="invalid-feedback">Cena jest wymagana i nie może być ujemna.</div>
            </div>
            <div class="mb-3">
                <label for="validity_hours" class="form-label">Ważność (godziny)</label>
                <input type="number" class="form-control" id="validity_hours" name="validity_hours" value="{{ old('validity_hours', $ticket->validity_hours) }}" required min="1" step="1">
                <div class="invalid-feedback">Ważność jest wymagana i musi wynosić co najmniej 1 godzinę (liczba całkowita).</div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $ticket->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktywny</label>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success">Zapisz zmiany</button>
                <a href="{{ route('dashboard', ['tab' => 'tickets']) }}" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
@endsection
