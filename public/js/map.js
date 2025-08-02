const RZ_PALETTE = {
    primary: '#0056b3',
    secondary: '#6c757d',
    accentSuccess: '#198754',
    accentDanger: '#dc3545',
    accentInfo: '#0dcaf0',
    accentWarning: '#ffc107',
    textDefault: '#212529',
};

class TransportMap {
    constructor() {
        this.map = null;
        this.markers = [];
        this.routingControls = [];
        this.defaultCenter = [50.011967, 21.962522];
        this.defaultZoom = 13;
        this.isCalculatingDistance = false;
        this.submitButton = null;
        this.polyline = null;
        this.currentMarker = null;
        this.init();
    }

    init() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        this.map = L.map('map').setView(this.defaultCenter, this.defaultZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        this.clearRoutingControls();
        this.clearMarkers();

        const viewContext = mapElement.dataset.viewContext || window.currentView;

        if (viewContext === 'line' && window.lineStops) {
            this.showLineRoute();
        } else if (viewContext === 'route-planner' || viewContext === 'ticket-purchase') {
            this.showAllStops();
            if (window.routes) {
                this.showRoutes();
            }
        } else if (viewContext === 'checkout' && window.routeData) {
            this.showCheckoutRoute();
        } else if (viewContext === 'stop-form') {
            this.initStopFormMap();
        } else if (window.currentStop) {
            this.showCurrentStop();
            if (window.lineStops) {
                this.showLineRoute();
            }
        } else if (window.stops) {
            this.showAllStops();
        }

        this.bindEvents();
    }

    initStopFormMap() {
        const latInput = document.getElementById('location_lat');
        const lonInput = document.getElementById('location_lon');

        if (!latInput || !lonInput) {
            console.error("Nie znaleziono pól input dla szerokości lub długości geograficznej.");
            return;
        }

        let initialLat = parseFloat(latInput.value) || this.defaultCenter[0];
        let initialLon = parseFloat(lonInput.value) || this.defaultCenter[1];
        let initialZoom = (latInput.value && lonInput.value && !isNaN(parseFloat(latInput.value)) && !isNaN(parseFloat(lonInput.value))) ? 15 : 7;


        this.map.setView([initialLat, initialLon], initialZoom);

        const updateMarkerAndInputs = (lat, lng, openPopup = true) => {
            if (this.currentMarker) {
                this.map.removeLayer(this.currentMarker);
            }
            this.currentMarker = L.marker([lat, lng]).addTo(this.map);
            if (openPopup) {
                this.currentMarker.bindPopup("Wybrana lokalizacja: <br>Szer: " + lat.toFixed(6) + "<br>Dł: " + lng.toFixed(6)).openPopup();
            }
            latInput.value = lat.toFixed(6);
            lonInput.value = lng.toFixed(6);
        };

        if (latInput.value && lonInput.value && !isNaN(parseFloat(latInput.value)) && !isNaN(parseFloat(lonInput.value))) {
            updateMarkerAndInputs(parseFloat(latInput.value), parseFloat(lonInput.value), false);
        }

        this.map.on('click', (e) => {
            updateMarkerAndInputs(e.latlng.lat, e.latlng.lng);
        });

        [latInput, lonInput].forEach(input => {
            input.addEventListener('change', () => {
                const lat = parseFloat(latInput.value);
                const lon = parseFloat(lonInput.value);
                if (!isNaN(lat) && !isNaN(lon)) {
                    if (this.currentMarker) {
                        this.currentMarker.setLatLng([lat, lon]);
                    } else {
                        this.currentMarker = L.marker([lat, lon]).addTo(this.map);
                    }
                    this.map.setView([lat, lon], this.map.getZoom() < 15 ? 15 : this.map.getZoom());
                }
            });
        });
    }

    async getIntermediateStops(lineId, fromStopId, toStopId) {
        try {
            const response = await fetch(`/api/lines/${lineId}/route-segment-stops?from_stop_id=${fromStopId}&to_stop_id=${toStopId}`);
            if (!response.ok) {
                console.error('Nie udało się pobrać przystanków pośrednich:', response.status, response.statusText);
                return null;
            }
            return await response.json();
        } catch (error) {
            console.error('Błąd podczas pobierania przystanków pośrednich:', error);
            return null;
        }
    }

    calculateStraightLineSegmentDistance(stop1, stop2) {
        const R = 6371;
        const dLat = this.deg2rad(stop2.location_lat - stop1.location_lat);
        const dLon = this.deg2rad(stop2.location_lon - stop1.location_lon);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(this.deg2rad(stop1.location_lat)) * Math.cos(this.deg2rad(stop2.location_lat)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    async calculateRouteDistance(callback) {
        const fromStopId = document.getElementById('from_stop_id')?.value;
        const toStopId = document.getElementById('to_stop_id')?.value;
        const lineId = document.getElementById('line_id')?.value;

        if (!fromStopId || !toStopId || !lineId) {
            console.warn('Brak wybranego przystanku lub linii do obliczenia dystansu.');
            if (callback) callback(false, 'missing_selection');
            return;
        }

        this.isCalculatingDistance = true;
        this.submitButton = document.querySelector('#ticketForm button[type="submit"]');
        if (this.submitButton) {
            this.submitButton.disabled = true;
            this.submitButton.dataset.originalText = this.submitButton.innerHTML;
            this.submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Obliczanie dystansu...';
        }

        let routeDistanceInput = document.getElementById('route_distance');
        if (!routeDistanceInput) {
            routeDistanceInput = document.createElement('input');
            routeDistanceInput.type = 'hidden';
            routeDistanceInput.id = 'route_distance';
            routeDistanceInput.name = 'route_distance';
            document.getElementById('ticketForm').appendChild(routeDistanceInput);
        }
        routeDistanceInput.value = 'CALCULATING';

        const intermediateStops = await this.getIntermediateStops(lineId, fromStopId, toStopId);

        if (!intermediateStops || intermediateStops.length < 2) {
            console.warn('Nie znaleziono wystarczającej liczby przystanków pośrednich do obliczenia dystansu.');
            routeDistanceInput.value = '0';
            this.isCalculatingDistance = false;
            if (this.submitButton) {
                this.submitButton.disabled = false;
                this.submitButton.innerHTML = this.submitButton.dataset.originalText || 'Oblicz cenę i kup bilet';
            }
            const isSameStop = fromStopId === toStopId;
            if (callback) callback(isSameStop, isSameStop ? 'same_stop' : 'no_intermediate_stops');
            return;
        }

        let totalCalculatedDistance;
        let osmCalculationCompletelySucceeded = true;
        const segmentPromises = [];

        for (let i = 0; i < intermediateStops.length - 1; i++) {
            const segmentStartStop = intermediateStops[i];
            const segmentEndStop = intermediateStops[i + 1];

            const waypoints = [
                L.latLng(segmentStartStop.location_lat, segmentStartStop.location_lon),
                L.latLng(segmentEndStop.location_lat, segmentEndStop.location_lon)
            ];

            const segmentPromise = new Promise((resolve) => {
                const segmentControl = L.Routing.control({
                    waypoints: waypoints,
                    show: false, addWaypoints: false, routeWhileDragging: false, fitSelectedRoutes: false,
                    createMarker: function() { return null; },
                    router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1', timeout: 5000 })
                });

                const segmentTimeoutID = setTimeout(() => {
                    segmentControl.remove();
                    console.warn(`Timeout OSRM dla segmentu ${segmentStartStop.stop_name} do ${segmentEndStop.stop_name}.`);
                    osmCalculationCompletelySucceeded = false;
                    resolve(this.calculateStraightLineSegmentDistance(segmentStartStop, segmentEndStop));
                }, 5500);

                segmentControl.on('routesfound', function(e) {
                    clearTimeout(segmentTimeoutID);
                    segmentControl.remove();
                    const route = e.routes[0];
                    if (route && route.summary && typeof route.summary.totalDistance !== 'undefined') {
                        resolve(route.summary.totalDistance / 1000);
                    } else {
                        console.warn(`OSRM znalazł trasę, ale brak danych dystansu dla segmentu ${segmentStartStop.stop_name} do ${segmentEndStop.stop_name}.`);
                        osmCalculationCompletelySucceeded = false;
                        resolve(window.transportMap.calculateStraightLineSegmentDistance(segmentStartStop, segmentEndStop));
                    }
                });

                segmentControl.on('routingerror', function(e) {
                    clearTimeout(segmentTimeoutID);
                    segmentControl.remove();
                    console.error(`Błąd routingu OSRM dla segmentu ${segmentStartStop.stop_name} do ${segmentEndStop.stop_name}:`, e.error);
                    osmCalculationCompletelySucceeded = false;
                    resolve(window.transportMap.calculateStraightLineSegmentDistance(segmentStartStop, segmentEndStop));
                });
                segmentControl.route();
            });
            segmentPromises.push(segmentPromise);
        }

        const segmentDistances = await Promise.all(segmentPromises);
        totalCalculatedDistance = segmentDistances.reduce((sum, distance) => sum + distance, 0);

        routeDistanceInput.value = totalCalculatedDistance.toFixed(2);
        console.log(`Ostateczny obliczony dystans (OSRM w pełni udany: ${osmCalculationCompletelySucceeded}): ${routeDistanceInput.value} km`);

        this.isCalculatingDistance = false;
        if (this.submitButton) {
            this.submitButton.disabled = false;
            this.submitButton.innerHTML = this.submitButton.dataset.originalText || 'Oblicz cenę i kup bilet';
        }

        if (callback) callback(true, osmCalculationCompletelySucceeded ? 'osrm_sum' : 'osrm_fallback_sum');
    }

    deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    clearRoutingControls() {
        this.routingControls.forEach(control => {
            if (this.map) this.map.removeControl(control);
        });
        this.routingControls = [];
    }

    showAllStops() {
        this.clearMarkers();
        if (!window.stops || !this.map) return;

        window.stops.forEach(stop => {
            const marker = L.marker([stop.location_lat, stop.location_lon], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="color: ${RZ_PALETTE.primary}"><i class="fas fa-map-marker-alt fa-2x"></i></div>`,
                    iconSize: [50, 50],
                    iconAnchor: [10, 20],
                    popupAnchor: [0, -20]
                })
            });

            const popupContent = document.createElement('div');
            popupContent.className = 'text-center';
            let content = `<strong>${stop.stop_name}</strong><br>`;

            const viewContext = document.getElementById('map')?.dataset.viewContext || window.currentView;

            if (viewContext === 'route-planner' || viewContext === 'ticket-purchase') {
                content += `
                <div class="btn-group mt-2" role="group">
                    <button onclick="window.transportMap.setFromStop('${stop.stop_id}')" class="btn btn-sm btn-success">
                        <i class="fas fa-play"></i> Przystanek początkowy
                    </button>
                    <button onclick="window.transportMap.setToStop('${stop.stop_id}')" class="btn btn-sm btn-danger">
                        <i class="fas fa-stop"></i> Przystanek końcowy
                    </button>
                </div>
                `;
            } else if (viewContext !== 'stop-form') {
                content += `
                <a href="/stops/${stop.stop_id}" class="btn btn-sm btn-outline-primary mt-2 text-primary bg-white">
                    Zobacz odjazdy
                </a>
                `;
            }

            popupContent.innerHTML = content;
            marker.bindPopup(popupContent);
            marker.addTo(this.map);
            this.markers.push(marker);
        });
    }

    showCurrentStop() {
        if (!window.currentStop || !this.map) return;

        const stop = window.currentStop;
        const marker = L.marker([stop.location_lat, stop.location_lon], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="color: ${RZ_PALETTE.primary}"><i class="fas fa-map-marker-alt fa-2x"></i></div>`,           iconSize: [50, 50],
                iconAnchor: [10, 20],
                popupAnchor: [0, -20]
            }),
            zIndexOffset: 2000
        })
            .bindPopup(`<strong>${stop.stop_name}</strong>`)
            .addTo(this.map);
        this.markers.push(marker);
        this.map.setView([stop.location_lat, stop.location_lon], 15);
    }

    showCheckoutRoute() {
        if (!window.routeData || !this.map) return;

        const fromStop = window.routeData.from_stop;
        const toStop = window.routeData.to_stop;

        if (!fromStop || !toStop || !fromStop.location_lat || !toStop.location_lat) {
            console.warn("Brak danych przystanków lub współrzędnych dla mapy na stronie checkout.");
            if (fromStop) this.addMarker(fromStop, RZ_PALETTE.accentSuccess, 'Przystanek początkowy');
            if (toStop) this.addMarker(toStop, RZ_PALETTE.accentDanger, 'Przystanek końcowy');
            if (fromStop && toStop && fromStop.location_lat && toStop.location_lat) {
                const bounds = L.latLngBounds([fromStop.location_lat, fromStop.location_lon], [toStop.location_lat, toStop.location_lon]);
                this.map.fitBounds(bounds, {padding: [50, 50]});
            } else if (fromStop && fromStop.location_lat) {
                this.map.setView([fromStop.location_lat, fromStop.location_lon], 13);
            } else if (toStop && toStop.location_lat) {
                this.map.setView([toStop.location_lat, toStop.location_lon], 13);
            }
            return;
        }

        this.clearMarkers();

        this.addMarker(fromStop, RZ_PALETTE.accentSuccess, 'Przystanek początkowy');
        this.addMarker(toStop, RZ_PALETTE.accentDanger, 'Przystanek końcowy');

        const control = L.Routing.control({
            waypoints: [
                L.latLng(fromStop.location_lat, fromStop.location_lon),
                L.latLng(toStop.location_lat, toStop.location_lon)
            ],
            show: false,
            addWaypoints: false,
            routeWhileDragging: false,
            lineOptions: {
                styles: [{color: RZ_PALETTE.primary, weight: 3, opacity: 0.7}]
            },
            createMarker: function() { return null; },
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1'
            })
        }).addTo(this.map);

        this.routingControls.push(control);

        control.on('routesfound', function(e) {
            const bounds = L.latLngBounds(e.routes[0].coordinates);
            window.transportMap.map.fitBounds(bounds, {padding: [50, 50]});
        });

        control.on('routingerror', function(e) {
            console.warn("Wizualizacja routingu na stronie checkout nie powiodła się:", e);
            const bounds = L.latLngBounds([fromStop.location_lat, fromStop.location_lon], [toStop.location_lat, toStop.location_lon]);
            window.transportMap.map.fitBounds(bounds, {padding: [50, 50]});
        });

        control.route();
        const bounds = L.latLngBounds([fromStop.location_lat, fromStop.location_lon], [toStop.location_lat, toStop.location_lon]);
        this.map.fitBounds(bounds, {padding: [50, 50]});
    }

    addMarker(stop, color, title) {
        const marker = L.marker([stop.location_lat, stop.location_lon], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="color: ${color}"><i class="fas fa-map-marker-alt fa-2x"></i></div>`,
                iconSize: [50, 50],
                iconAnchor: [10, 20]
            })
        }).addTo(this.map).bindPopup(`<strong>${title}:</strong><br>${stop.name || stop.stop_name}`);
        this.markers.push(marker);
        return marker;
    }

    showLineRoute() {
        if (!window.lineStops || !window.lineStops.length || !this.map) return;

        this.clearMarkers();
        const currentStopId = window.currentStop?.stop_id;
        const waypoints = [];

        window.lineStops.forEach((stop, index) => {
            let markerColor;
            let isSpecialStop = false;

            if (stop.stop_id === currentStopId) {
                markerColor = RZ_PALETTE.primary;
                isSpecialStop = true;
            } else if (index === 0) {
                markerColor = RZ_PALETTE.accentSuccess;
                isSpecialStop = true;
            } else if (index === window.lineStops.length - 1) {
                markerColor = RZ_PALETTE.accentDanger;
                isSpecialStop = true;
            } else {
                markerColor = RZ_PALETTE.secondary;
            }

            const iconOptions = {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="color: ${markerColor}">
                    <i class="fas fa-map-marker-alt${isSpecialStop ? ' fa-2x' : ''}"></i>
                </div>`,
                    iconSize: [isSpecialStop ? 50 : 40, isSpecialStop ? 50 : 40],
                    iconAnchor: [10, 20],
                    popupAnchor: [0, -20]
                })
            };

            const marker = L.marker([stop.location_lat, stop.location_lon], iconOptions)
                .bindPopup(`
                <div class="text-center">
                    <strong>${stop.stop_name}</strong><br>
                    <span class="badge bg-secondary">${stop.sequence}</span>
                </div>
            `)
                .addTo(this.map);

            if (stop.stop_id === currentStopId) {
                marker.setZIndexOffset(2000);
            }

            this.markers.push(marker);
            waypoints.push(L.latLng(stop.location_lat, stop.location_lon));
        });

        if (waypoints.length >= 2) {
            const control = L.Routing.control({
                waypoints: waypoints,
                show: false,
                addWaypoints: false,
                routeWhileDragging: false,
                fitSelectedRoutes: false,
                showAlternatives: false,
                createMarker: function() { return null; },
                lineOptions: {
                    styles: [{
                        color: RZ_PALETTE.primary,
                        weight: 3,
                        opacity: 0.7
                    }]
                },
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                })
            }).addTo(this.map);

            this.routingControls.push(control);
        }

        if (waypoints.length > 0) {
            const bounds = L.latLngBounds(waypoints);
            this.map.fitBounds(bounds, {padding: [50, 50]});
        }
    }

    showRoutes() {
        if (!window.routes || !window.routes.length || !this.map) return;
        this.clearMarkers();
        this.routes = window.routes;
        this.selectRoute(0);
    }

    selectRoute(index) {
        const route = this.routes[index];
        if (!route || !route.all_stops || !this.map) return;

        this.clearMarkers();

        document.querySelectorAll('.route-details').forEach(el => {
            el.style.display = 'none';
        });
        const routeDetails = document.getElementById(`route-details-${index}`);
        if (routeDetails) {
            routeDetails.style.display = 'block';
        }

        route.all_stops.forEach(stop => {
            const marker = L.marker([stop.location_lat, stop.location_lon], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="color: ${
                        stop.is_start ? RZ_PALETTE.accentSuccess :
                            stop.is_end ? RZ_PALETTE.accentDanger :
                                RZ_PALETTE.secondary
                    }"><i class="fas fa-map-marker-alt${stop.is_start || stop.is_end ? ' fa-2x' : ''}"></i></div>`,
                    iconSize: [stop.is_start || stop.is_end ? 80 : 60,
                        stop.is_start || stop.is_end ? 80 : 60],
                    iconAnchor: [10, 20],
                    popupAnchor: [0, -20]
                }),
                zIndexOffset: stop.is_start || stop.is_end ? 1000 : 0
            });

            marker.bindPopup(`
                <div class="text-center">
                    <strong>${stop.stop_name}</strong><br>
                    ${stop.is_start ? `<div class="text-success small">Odjazd: ${route.departure_time}</div>` : ''}
                    ${stop.is_end ? `<div class="text-danger small">Przyjazd: ${route.arrival_time}</div>` : ''}
                </div>
            `);
            marker.addTo(this.map);
            this.markers.push(marker);
        });

        const waypoints = route.all_stops.map(stop =>
            L.latLng(stop.location_lat, stop.location_lon)
        );

        const control = L.Routing.control({
            waypoints: waypoints,
            show: false,
            addWaypoints: false,
            routeWhileDragging: false,
            fitSelectedRoutes: false,
            showAlternatives: false,
            createMarker: function() { return null; },
            lineOptions: { styles: [{color: RZ_PALETTE.primary, weight: 3, opacity: 0.7}] }
        }).addTo(this.map);
        this.routingControls.push(control);
        const bounds = L.latLngBounds(waypoints);
        this.map.fitBounds(bounds, {padding: [50, 50]});
    }

    clearMarkers() {
        if (!this.map) return;

        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];

        if (this.currentMarker) {
            this.map.removeLayer(this.currentMarker);
            this.currentMarker = null;
        }

        this.routingControls.forEach(control => {
            this.map.removeControl(control);
        });
        this.routingControls = [];

        if (this.polyline) {
            this.map.removeLayer(this.polyline);
            this.polyline = null;
        }
    }

    setFromStop(stopId) {
        const fromInput = document.getElementById('fromInput');
        const fromHidden = document.getElementById('from') || document.getElementById('from_stop_id');
        if (fromHidden && fromInput) {
            fromHidden.value = stopId;

            const stop = window.stops.find(s => s.stop_id.toString() === stopId.toString());
            if (stop) {
                fromInput.value = stop.stop_name;

                const dropdown = document.getElementById('fromDropdown');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
            }

            fromHidden.dispatchEvent(new Event('change'));
        }
        if (this.map) this.map.closePopup();
    }

    setToStop(stopId) {
        const toInput = document.getElementById('toInput');
        const toHidden = document.getElementById('to') || document.getElementById('to_stop_id');
        if (toHidden && toInput) {
            toHidden.value = stopId;

            const stop = window.stops.find(s => s.stop_id.toString() === stopId.toString());
            if (stop) {
                toInput.value = stop.stop_name;

                const dropdown = document.getElementById('toDropdown');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
            }

            toHidden.dispatchEvent(new Event('change'));
        }
        if (this.map) this.map.closePopup();
    }

    bindEvents() {
        const searchInput = document.getElementById('searchStop');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.filterStops(e.target.value));
        }

        const ticketForm = document.getElementById('ticketForm');
        if (ticketForm && (document.getElementById('map')?.dataset.viewContext === 'ticket-purchase' || window.currentView === 'ticket-purchase')) {
            ticketForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!this.validateForm()) {
                    return;
                }

                if (this.isCalculatingDistance) {
                    console.log('Obliczanie dystansu jest już w toku.');
                    return;
                }

                const routeDistanceEl = document.getElementById('route_distance');
                const currentDistanceValue = routeDistanceEl ? routeDistanceEl.value : null;

                if (!routeDistanceEl || currentDistanceValue === '0' || currentDistanceValue === 'CALCULATING' || currentDistanceValue === 'ERROR_NO_STOPS') {
                    console.log('Uruchamianie obliczania dystansu...');
                    await this.calculateRouteDistance((success, method) => {
                        const finalDistanceValue = parseFloat(document.getElementById('route_distance').value);

                        if (success && finalDistanceValue >= 0) {
                            console.log(`Obliczanie dystansu zakończone (${method}). Ostateczny dystans: ${finalDistanceValue}. Wysyłanie formularza.`);
                            ticketForm.submit();
                        } else {
                            console.error(`Obliczanie dystansu nie powiodło się lub dało wynik ujemny (${method}). Ostateczny dystans: ${finalDistanceValue}.`);
                            alert('Nie udało się obliczyć dystansu dla wybranej trasy. Sprawdź wybrane przystanki i linię lub spróbuj ponownie.');
                            this.isCalculatingDistance = false;
                            if (this.submitButton) {
                                this.submitButton.disabled = false;
                                this.submitButton.innerHTML = this.submitButton.dataset.originalText || 'Oblicz cenę i kup bilet';
                            }
                        }
                    });
                } else {
                    console.log('Używanie wcześniej obliczonego dystansu. Wysyłanie formularza.');
                    ticketForm.submit();
                }
            });
        }

        const fromStopInput = document.getElementById('from_stop_id');
        const toStopInput = document.getElementById('to_stop_id');
        const lineSelect = document.getElementById('line_id');

        if (fromStopInput && toStopInput && lineSelect && (document.getElementById('map')?.dataset.viewContext === 'ticket-purchase' || window.currentView === 'ticket-purchase')) {
            [fromStopInput, toStopInput, lineSelect].forEach(input => {
                input.addEventListener('change', () => {
                    const routeDistance = document.getElementById('route_distance');
                    if (routeDistance) {
                        routeDistance.value = '0';
                        console.log('Zmieniono wybór trasy, dystans zresetowany do 0.');
                    }
                    this.clearRoutingControls();
                });
            });
        }
    }

    validateForm() {
        const fromStopId = document.getElementById('from_stop_id')?.value;
        const toStopId = document.getElementById('to_stop_id')?.value;
        const lineId = document.getElementById('line_id')?.value;
        const ticketId = document.getElementById('ticket_id')?.value;

        let isValid = true;

        if (!fromStopId) {
            alert('Wybierz przystanek początkowy.');
            isValid = false;
        } else if (!toStopId) {
            alert('Wybierz przystanek końcowy.');
            isValid = false;
        } else if (!lineId) {
            alert('Wybierz linię.');
            isValid = false;
        } else if (!ticketId) {
            alert('Wybierz rodzaj biletu.');
            isValid = false;
        }

        return isValid;
    }

    filterStops(query) {
        const stopsList = document.getElementById('stops-list');
        if (!stopsList) return;

        const normalizedQuery = query.toLowerCase().trim();
        Array.from(stopsList.children).forEach(item => {
            const stopName = item.textContent.toLowerCase();
            item.style.display = stopName.includes(normalizedQuery) ? '' : 'none';
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.transportMap = new TransportMap();
});
