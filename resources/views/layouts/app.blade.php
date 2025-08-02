<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Rozkład Jazdy') | P.Z.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <meta name="lines-data" content="{{ json_encode($lines ?? []) }}">
    @stack('styles')
</head>
<body class="{{ session('highContrastPreferred') ? 'high-contrast-mode' : '' }} {{ session('fontSizePreferred', 'font-size-md') }}">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('stops.index') }}">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" alt="Logo Firmy" height="35" class="me-2 rounded">
            @elseif(file_exists(public_path('images/logo.svg')))
                <img src="{{ asset('images/logo.svg') }}" alt="Logo Firmy" height="35" class="me-2 rounded">
            @endif
            Prywatne Zakłady Autobusowe
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('stops.index') }}">Odjazdy z przystanku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('lines.index') }}">Rozkład linii</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('route-planner') }}">Planowanie trasy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tickets.index') }}">Bilety</a>
                </li>
            </ul>

            <div class="navbar-nav ms-auto d-flex align-items-center">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Logowanie</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(Auth::user()->profile_photo_path)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Avatar" class="rounded-circle me-2" width="30" height="30">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('tickets.my-tickets') }}">Moje bilety</a></li>
                            @if(Auth::user()->isAdmin())
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Panel administracyjny</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Wyloguj</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest

                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="accessibilityDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ustawienia dostępności">
                        <i class="fas fa-cog"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="accessibilityDropdown" style="min-width: 250px;">
                        <li class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="contrast-switch-input" title="Przełącz kontrast">
                                <label class="form-check-label" for="contrast-switch-input" title="Przełącz kontrast">
                                    <i class="fas fa-adjust me-1"></i> Kontrast
                                </label>
                            </div>
                        </li>
                        <li>
                            <label for="font-size-slider" class="form-label small mb-1 d-block">
                                <i class="fas fa-text-height me-1"></i> Rozmiar czcionki
                            </label>
                            <div class="d-flex align-items-center">
                                <span title="Zmniejsz czcionkę" class="me-2"><i class="fas fa-font" style="font-size: 0.8em;"></i></span>
                                <input type="range" class="form-range accessibility-slider" id="font-size-slider" min="0" max="2" step="1" title="Zmień rozmiar czcionki">
                                <span title="Powiększ czcionkę" class="ms-2"><i class="fas fa-font" style="font-size: 1.2em;"></i></span>
                            </div>
                        </li>
                    </ul>
                </li>
            </div>
        </div>
    </div>
</nav>

<main class="py-2">
    @if(session('success') || session('error') || session('warning') || session('info') || session('unique_error') || $errors->any())
        <div class="container my-2">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                </div>
            @endif

            @if(session('unique_error'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Błąd unikalności:</strong>
                        <span class="ms-2">{{ session('unique_error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                </div>
            @endif

            @if($errors->any() && !request()->is('route-planner*'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                </div>
            @endif
        </div>
    @endif

    @yield('content')
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form.needs-validation');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 5000);
        });

        const body = document.body;
        const contrastSwitch = document.getElementById('contrast-switch-input');
        const contrastPreferenceKey = 'highContrastPreferred';

        const highContrastPreferred = localStorage.getItem(contrastPreferenceKey) === 'true';
        if (highContrastPreferred) {
            body.classList.add('high-contrast-mode');
            if(contrastSwitch) contrastSwitch.checked = true;
        }

        if (contrastSwitch) {
            contrastSwitch.addEventListener('change', function() {
                body.classList.toggle('high-contrast-mode', this.checked);
                if (this.checked) {
                    localStorage.setItem(contrastPreferenceKey, 'true');
                } else {
                    localStorage.removeItem(contrastPreferenceKey);
                }
            });
        }

        const fontSizeSlider = document.getElementById('font-size-slider');
        const fontSizePreferenceKey = 'fontSizePreferred';
        const fontSizes = ['font-size-sm', 'font-size-md', 'font-size-lg'];
        const defaultFontSizeClass = 'font-size-md';

        function applyFontSize(sizeClass) {
            fontSizes.forEach(cls => body.classList.remove(cls));
            if (sizeClass && fontSizes.includes(sizeClass)) {
                body.classList.add(sizeClass);
                localStorage.setItem(fontSizePreferenceKey, sizeClass);
            } else {
                body.classList.add(defaultFontSizeClass);
                localStorage.setItem(fontSizePreferenceKey, defaultFontSizeClass);
            }
        }

        const storedFontSizeClass = localStorage.getItem(fontSizePreferenceKey) || defaultFontSizeClass;
        applyFontSize(storedFontSizeClass);

        if (fontSizeSlider) {
            const initialSliderValue = fontSizes.indexOf(storedFontSizeClass);
            if (initialSliderValue !== -1) {
                fontSizeSlider.value = initialSliderValue;
            } else {
                fontSizeSlider.value = fontSizes.indexOf(defaultFontSizeClass);
            }

            fontSizeSlider.addEventListener('input', function() {
                const selectedSizeClass = fontSizes[parseInt(this.value)];
                applyFontSize(selectedSizeClass);
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>
