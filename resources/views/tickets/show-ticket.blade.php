@extends('layouts.app')

@section('page_title', 'Szczegóły biletu #' . $purchasedTicket->purchase_id)

@section('content')
    <div class="container">
        <div class="card auto-height-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Bilet {{ $purchasedTicket->ticket->ticket_name }}</h5>
            </div>
            <div class="card-body auto-height">
                <div class="text-center mb-3">
                    <span class="badge bg-{{ $purchasedTicket->valid_until > now() && $purchasedTicket->status === 'aktywny' ? 'success' : 'danger' }} fs-6 p-2">
                        {{ $purchasedTicket->valid_until > now() && $purchasedTicket->status === 'aktywny' ? 'Ważny' : 'Nieważny/Wygasły' }}
                    </span>
                </div>

                <div class="row mb-2">
                    <div class="col-5 fw-bold">Nr biletu:</div>
                    <div class="col-7">{{ $purchasedTicket->purchase_id }}</div>
                </div>

                @if($purchasedTicket->transaction && $purchasedTicket->transaction->from_stop_id && $purchasedTicket->transaction->to_stop_id)
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Trasa:</div>
                        <div class="col-7">
                            {{ $purchasedTicket->transaction->fromStop->stop_name }} →
                            {{ $purchasedTicket->transaction->toStop->stop_name }}
                        </div>
                    </div>
                @endif

                @if($purchasedTicket->transaction && $purchasedTicket->transaction->line_id)
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Linia:</div>
                        <div class="col-7">{{ $purchasedTicket->transaction->line->line_number }}</div>
                    </div>
                @endif

                <div class="row mb-2">
                    <div class="col-5 fw-bold">Ważny od:</div>
                    <div class="col-7">{{ $purchasedTicket->valid_from->format('d.m.Y H:i') }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 fw-bold">Ważny do:</div>
                    <div class="col-7">{{ $purchasedTicket->valid_until->format('d.m.Y H:i') }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 fw-bold">Status (zapisany):</div>
                    <div class="col-7">{{ ucfirst($purchasedTicket->status) }}</div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('tickets.my-tickets') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Powrót do biletów
                </a>
            </div>
        </div>

        @if($purchasedTicket->transaction && $purchasedTicket->transaction->from_stop_id && $purchasedTicket->transaction->to_stop_id && isset($intermediateStopsData) && $intermediateStopsData->count() >= 2)
            <div class="card mt-4 auto-height-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Mapa trasy</h5>
                </div>
                <div class="card-body p-0 auto-height">
                    <div id="map" style="height: 400px;"></div>
                </div>
            </div>
        @elseif($purchasedTicket->transaction && ($purchasedTicket->transaction->from_stop_id || $purchasedTicket->transaction->to_stop_id))
            <div class="card mt-4 auto-height-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Lokalizacja przystanku/ów</h5>
                </div>
                <div class="card-body p-0 auto-height">
                    <div id="map" style="height: 300px;"></div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @if($purchasedTicket->transaction && ($purchasedTicket->transaction->from_stop_id || $purchasedTicket->transaction->to_stop_id))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const RZ_PALETTE = {
                    primary: '#0056b3',
                    secondary: '#6c757d',
                    accentSuccess: '#198754',
                    accentDanger: '#dc3545',
                    accentInfo: '#0dcaf0',
                    accentWarning: '#ffc107',
                    textDefault: '#212529',
                };

                const mapElement = document.getElementById('map');
                if (!mapElement) return;

                const map = L.map('map');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                const fromStop = @json($purchasedTicket->transaction->fromStop);
                const toStop = @json($purchasedTicket->transaction->toStop);
                const intermediateStops = @json($intermediateStopsData);

                let waypoints = [];
                let markers = [];

                if (intermediateStops && intermediateStops.length >= 2) {
                    intermediateStops.forEach(stop => {
                        waypoints.push(L.latLng(stop.location_lat, stop.location_lon));
                        let markerColor = RZ_PALETTE.secondary;
                        let iconSize = [40,40];
                        let zIndex = 0;

                        if (stop.is_start) {
                            markerColor = RZ_PALETTE.accentSuccess;
                            iconSize = [50,50];
                            zIndex = 1000;
                        } else if (stop.is_end) {
                            markerColor = RZ_PALETTE.accentDanger;
                            iconSize = [50,50];
                            zIndex = 1000;
                        }

                        const marker = L.marker([stop.location_lat, stop.location_lon], {
                            icon: L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="color: ${markerColor}"><i class="fas fa-map-marker-alt ${stop.is_start || stop.is_end ? 'fa-2x' : ''}"></i></div>`,
                                iconSize: iconSize,
                                iconAnchor: [10, 20]
                            }),
                            zIndexOffset: zIndex
                        }).addTo(map).bindPopup(`<strong>${stop.stop_name}</strong>`);
                        markers.push(marker);
                    });

                    L.Routing.control({
                        waypoints: waypoints,
                        show: false,
                        addWaypoints: false,
                        routeWhileDragging: false,
                        fitSelectedRoutes: false,
                        lineOptions: {
                            styles: [{color: RZ_PALETTE.primary, weight: 3, opacity: 0.7}]
                        },
                        createMarker: function() { return null; }
                    }).addTo(map);

                    if (waypoints.length > 0) {
                        const bounds = L.latLngBounds(waypoints);
                        map.fitBounds(bounds, {padding: [50, 50]});
                    }

                } else {
                    if (fromStop && fromStop.location_lat) {
                        L.marker([fromStop.location_lat, fromStop.location_lon], {
                            icon: L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="color: ${RZ_PALETTE.accentSuccess}"><i class="fas fa-map-marker-alt fa-2x"></i></div>`,
                                iconSize: [50, 50], iconAnchor: [10, 20]
                            })
                        }).addTo(map).bindPopup('<strong>Przystanek początkowy:</strong><br>' + fromStop.stop_name);
                        waypoints.push(L.latLng(fromStop.location_lat, fromStop.location_lon));
                    }
                    if (toStop && toStop.location_lat) {
                        L.marker([toStop.location_lat, toStop.location_lon], {
                            icon: L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="color: ${RZ_PALETTE.accentDanger}"><i class="fas fa-map-marker-alt fa-2x"></i></div>`,
                                iconSize: [50, 50], iconAnchor: [10, 20]
                            })
                        }).addTo(map).bindPopup('<strong>Przystanek końcowy:</strong><br>' + toStop.stop_name);
                        waypoints.push(L.latLng(toStop.location_lat, toStop.location_lon));
                    }

                    if (waypoints.length > 0) {
                        if (waypoints.length === 1) {
                            map.setView(waypoints[0], 14);
                        } else {
                            const bounds = L.latLngBounds(waypoints);
                            map.fitBounds(bounds, {padding: [50, 50]});
                        }
                    } else {
                        map.setView([50.0647, 19.9450], 12);
                    }
                }
            });
        </script>
    @endif
@endpush
