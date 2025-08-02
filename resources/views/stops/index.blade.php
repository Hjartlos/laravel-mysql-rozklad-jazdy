@extends('layouts.app')

@section('page_title', 'Odjazdy z przystanku')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="searchStop"
                                   placeholder="Wyszukaj przystanek..."
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="list-group list-group-flush scrollable" id="stopsList">
                        @foreach($stops as $stop)
                            <a href="{{ route('stops.show', $stop->stop_id) }}"
                               class="list-group-item list-group-item-action stop-item"
                               data-stop-name="{{ strtolower($stop->stop_name) }}">
                                {{ $stop->stop_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div id="map" class="content-height"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            window.stops = @json($stops);

            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchStop');
                let typingTimer;

                searchInput.addEventListener('input', function(e) {
                    clearTimeout(typingTimer);

                    typingTimer = setTimeout(() => {
                        const searchText = e.target.value.toLowerCase().trim();
                        const stopItems = document.querySelectorAll('.stop-item');

                        stopItems.forEach(item => {
                            const stopName = item.dataset.stopName;
                            item.style.display = stopName.includes(searchText) ? '' : 'none';
                        });

                        if (window.transportMap) {
                            window.transportMap.filterMarkers(searchText);
                        }
                    }, 100);
                });
            });

        </script>
        <script src="{{ asset('js/map.js') }}"></script>
    @endpush
@endsection
