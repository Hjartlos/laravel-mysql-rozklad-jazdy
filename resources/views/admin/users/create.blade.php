@extends('layouts.app')

@section('page_title', 'Dodaj nowego użytkownika')

@section('content')
    <div class="container">
        <h1>Dodaj nowego użytkownika</h1>
        <form action="{{ route('admin.users.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Imię i nazwisko</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
                <div class="invalid-feedback">Imię i nazwisko jest wymagane.</div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Adres e-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required maxlength="255">
                <div class="invalid-feedback">Podaj poprawny adres email.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Hasło</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                <div class="invalid-feedback">Hasło musi mieć co najmniej 8 znaków.</div>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Potwierdzenie hasła</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
                <div class="invalid-feedback">Potwierdzenie hasła jest wymagane i musi mieć co najmniej 8 znaków.</div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_admin" value="0">
                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_admin">Administrator</label>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success">Zapisz</button>
                <a href="{{ route('dashboard', ['tab' => 'users']) }}" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
@endsection
