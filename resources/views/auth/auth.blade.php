@extends('layouts.app')

@section('page_title', 'Logowanie / Rejestracja')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card auto-height-card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ !request()->is('register') ? 'active' : '' }}" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-form" type="button" role="tab" aria-controls="login-form" aria-selected="{{ !request()->is('register') ? 'true' : 'false' }}">Logowanie</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ request()->is('register') ? 'active' : '' }}" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-form" type="button" role="tab" aria-controls="register-form" aria-selected="{{ request()->is('register') ? 'true' : 'false' }}">Rejestracja</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body auto-height">
                        <div class="tab-content">
                            <div class="tab-pane fade {{ !request()->is('register') ? 'show active' : '' }}" id="login-form" role="tabpanel" aria-labelledby="login-tab">
                                <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control"
                                               id="email" name="email" value="{{ old('email') }}" required>
                                        <div class="invalid-feedback">Podaj poprawny adres email.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Hasło</label>
                                        <input type="password" class="form-control"
                                               id="password" name="password" required>
                                        <div class="invalid-feedback">Hasło jest wymagane.</div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Zaloguj</button>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p>Nie masz jeszcze konta? <a href="#" id="go-to-register">Zarejestruj się</a></p>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade {{ request()->is('register') ? 'show active' : '' }}" id="register-form" role="tabpanel" aria-labelledby="register-tab">
                                <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Imię</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name') }}" required maxlength="255">
                                        <div class="invalid-feedback">Imię jest wymagane.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="register_email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="register_email" name="email" value="{{ old('email') }}" required maxlength="255">
                                        <div class="invalid-feedback">Podaj poprawny adres email.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="register_password" class="form-label">Hasło</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="register_password" name="password" required minlength="8">
                                        <div class="invalid-feedback">Hasło musi mieć co najmniej 8 znaków.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Potwierdź hasło</label>
                                        <input type="password" class="form-control"
                                               id="password_confirmation" name="password_confirmation" required minlength="8">
                                        <div class="invalid-feedback">Potwierdzenie hasła jest wymagane i musi mieć co najmniej 8 znaków.</div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Zarejestruj</button>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p>Masz już konto? <a href="#" id="go-to-login">Zaloguj się</a></p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            const goToRegister = document.getElementById('go-to-register');
            const goToLogin = document.getElementById('go-to-login');

            goToRegister?.addEventListener('click', function(e) {
                e.preventDefault();
                registerTab.click();
            });

            goToLogin?.addEventListener('click', function(e) {
                e.preventDefault();
                loginTab.click();
            });

            const hasRegisterErrors = {{ $errors->any() && (old('name') !== null || old('email') !== null || $errors->has('password')) ? 'true' : 'false' }};

            if (hasRegisterErrors) {
                registerTab.click();
            }
        });
    </script>
@endsection
