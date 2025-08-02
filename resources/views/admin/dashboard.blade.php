@extends('layouts.app')

@section('page_title', 'Panel administracyjny')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Panel Zarządzania</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('dashboard', ['tab' => 'statistics']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'statistics' ? 'active' : '' }}" data-tab="statistics">
                            <i class="fas fa-chart-line me-2"></i>Statystyki
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'stops']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'stops' ? 'active' : '' }}" data-tab="stops">
                            <i class="fas fa-map-marker-alt me-2"></i>Przystanki
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'lines']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'lines' ? 'active' : '' }}" data-tab="lines">
                            <i class="fas fa-bus me-2"></i>Linie
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'timetable']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'timetable' ? 'active' : '' }}" data-tab="timetable">
                            <i class="fas fa-clock me-2"></i>Rozkłady
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'operatingdays']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'operatingdays' ? 'active' : '' }}" data-tab="operatingdays">
                            <i class="fas fa-calendar-alt me-2"></i>Dni kursowania
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'tickets']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'tickets' ? 'active' : '' }}" data-tab="tickets">
                            <i class="fas fa-ticket-alt me-2"></i>Bilety
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'purchasedtickets']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'purchasedtickets' ? 'active' : '' }}" data-tab="purchasedtickets">
                            <i class="fas fa-receipt me-2"></i>Zakupione bilety
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'transactions']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'transactions' ? 'active' : '' }}" data-tab="transactions">
                            <i class="fas fa-credit-card me-2"></i>Transakcje
                        </a>
                        <a href="{{ route('dashboard', ['tab' => 'users']) }}" class="list-group-item list-group-item-action {{ $activeTab == 'users' ? 'active' : '' }}" data-tab="users">
                            <i class="fas fa-users me-2"></i>Użytkownicy
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                @if($activeTab == 'statistics')
                    @include('admin.statistics.index')
                @elseif($activeTab == 'stops')
                    @include('admin.stops.index')
                @elseif($activeTab == 'lines')
                    @include('admin.lines.index')
                @elseif($activeTab == 'timetable')
                    @include('admin.timetable.index')
                @elseif($activeTab == 'operatingdays')
                    @include('admin.operatingdays.index')
                @elseif($activeTab == 'users')
                    @include('admin.users.index')
                @elseif($activeTab == 'tickets')
                    @include('admin.tickets.index')
                @elseif($activeTab == 'transactions')
                    @include('admin.transactions.index')
                @elseif($activeTab == 'purchasedtickets')
                    @include('admin.purchasedtickets.index')
                @else
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Wybierz sekcję z menu</h5>
                        </div>
                        <div class="card-body">
                            <p>Witaj w panelu administracyjnym. Wybierz sekcję z menu po lewej stronie, aby zarządzać systemem.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.list-group-item-action[data-tab]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tab = this.getAttribute('data-tab');
                    localStorage.setItem('adminActiveTab', tab);
                    window.location.href = this.href;
                });
            });

            const urlParams = new URLSearchParams(window.location.search);
            let tabParam = urlParams.get('tab');

            if (!tabParam) {
                const savedTab = localStorage.getItem('adminActiveTab');
                if (savedTab) {
                    tabParam = savedTab;
                } else {
                    tabParam = 'statistics';
                }
                const newUrl = `${window.location.pathname}?tab=${tabParam}`;
                history.replaceState({ path: newUrl }, '', newUrl);
            } else {
                localStorage.setItem('adminActiveTab', tabParam);
            }
        });
    </script>
@endpush
