@extends('layouts.app')

@section('page_title', 'Dodaj nowy dzień kursowania')

@section('content')
    <div class="container">
        <h1>Dodaj nowy dzień kursowania</h1>
        <form action="{{ route('admin.operatingdays.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nazwa dnia kursowania</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
                <div class="invalid-feedback">Nazwa dnia kursowania jest wymagana.</div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Opis</label>
                <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000">{{ old('description') }}</textarea>
                <small class="text-muted">Np. "Dni robocze", "Soboty, niedziele i święta"</small>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success">Zapisz</button>
                <a href="{{ route('dashboard', ['tab' => 'operatingdays']) }}" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
@endsection
