<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Statystyki Systemu</h5>
    </div>
    <div class="card-body">
        <h4>Ogólne podsumowanie</h4>
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ $totalUsers ?? 0 }}</h5>
                        <p class="card-text">Użytkowników</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ $totalLines ?? 0 }}</h5>
                        <p class="card-text">Linii</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ $totalStops ?? 0 }}</h5>
                        <p class="card-text">Przystanków</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ $totalTicketTypes ?? 0 }}</h5>
                        <p class="card-text">Rodzajów Biletów</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ $totalPurchasedTickets ?? 0 }}</h5>
                        <p class="card-text">Zakupionych Biletów</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ $totalTransactions ?? 0 }}</h5>
                        <p class="card-text">Transakcji</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mb-4">
                <div class="card text-center shadow-sm bg-success text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title display-6">{{ number_format($totalRevenue ?? 0, 2) }} zł</h5>
                        <p class="card-text">Całkowity Przychód</p>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <form method="GET" action="{{ route('dashboard') }}" class="mb-4 p-3 border rounded bg-light needs-validation" novalidate id="statisticsFilterForm">
            <input type="hidden" name="tab" value="statistics">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="filter_start_date" class="form-label">Data początkowa</label>
                    <input type="date" class="form-control form-control-sm @error('filter_start_date') is-invalid @enderror" id="filter_start_date" name="filter_start_date" value="{{ $filterStartDate ? $filterStartDate->format('Y-m-d') : '' }}" required>
                    <div class="invalid-feedback">
                        @error('filter_start_date')
                        {{ $message }}
                        @else
                            Data początkowa jest wymagana.
                            @enderror
                    </div>
                </div>
                <div class="col-md-5">
                    <label for="filter_end_date" class="form-label">Data końcowa</label>
                    <input type="date" class="form-control form-control-sm @error('filter_end_date') is-invalid @enderror" id="filter_end_date" name="filter_end_date" value="{{ $filterEndDate ? $filterEndDate->format('Y-m-d') : '' }}" required>
                    <div class="invalid-feedback">
                        @error('filter_end_date')
                        {{ $message }}
                        @else
                            Data końcowa jest wymagana.
                            @enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm w-100">Filtruj</button>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm auto-height-card">
                    <div class="card-header">Sprzedaż biletów ({{ $filterStartDate ? $filterStartDate->format('d.m.Y') : 'Brak daty' }} - {{ $filterEndDate ? $filterEndDate->format('d.m.Y') : 'Brak daty' }})</div>
                    <div class="card-body auto-height">
                        <canvas id="ticketSalesChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm auto-height-card">
                    <div class="card-header">Popularne typy biletów ({{ $filterStartDate ? $filterStartDate->format('d.m.Y') : 'Brak daty' }} - {{ $filterEndDate ? $filterEndDate->format('d.m.Y') : 'Brak daty' }})</div>
                    <div class="card-body auto-height">
                        <canvas id="popularTicketTypesChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm auto-height-card">
                    <div class="card-header">Rejestracje użytkowników ({{ $filterStartDate ? $filterStartDate->format('d.m.Y') : 'Brak daty' }} - {{ $filterEndDate ? $filterEndDate->format('d.m.Y') : 'Brak daty' }})</div>
                    <div class="card-body auto-height">
                        <canvas id="userRegistrationsChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartColors = {
                primary: 'rgba(13, 110, 253, 0.7)',
                secondary: 'rgba(108, 117, 125, 0.7)',
                success: 'rgba(25, 135, 84, 0.7)',
                info: 'rgba(13, 202, 240, 0.7)',
                warning: 'rgba(255, 193, 7, 0.7)',
                lightBlue: 'rgba(173, 216, 230, 0.7)'
            };
            const chartBorderColors = {
                primary: 'rgb(13, 110, 253)',
                secondary: 'rgb(108, 117, 125)',
                success: 'rgb(25, 135, 84)',
                info: 'rgb(13, 202, 240)',
                warning: 'rgb(255, 193, 7)',
                lightBlue: 'rgb(173, 216, 230)'
            };
            function initChart(canvasId, chartType, labels, data, datasetLabel, customOptions = {}) {
                const ctx = document.getElementById(canvasId);
                if (!ctx || typeof Chart === 'undefined') return null;
                if (window[canvasId + 'Instance']) window[canvasId + 'Instance'].destroy();
                const defaultOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: chartType !== 'pie' && chartType !== 'doughnut' } }
                };
                const chartOptions = {...defaultOptions, ...customOptions };
                if (chartType === 'bar' && customOptions.indexAxis === 'y') {
                    chartOptions.scales.x = { beginAtZero: true, ticks: { precision: 0 } };
                    delete chartOptions.scales.y;
                }
                let backgroundColor, borderColor;
                if (canvasId === 'ticketSalesChart') {
                    backgroundColor = chartColors.primary; borderColor = chartBorderColors.primary;
                } else if (canvasId === 'userRegistrationsChart') {
                    backgroundColor = chartColors.info; borderColor = chartBorderColors.info;
                } else if (canvasId === 'popularTicketTypesChart') {
                    backgroundColor = [chartColors.primary, chartColors.success, chartColors.warning, chartColors.secondary, chartColors.lightBlue, chartColors.info];
                    borderColor = [chartBorderColors.primary, chartBorderColors.success, chartBorderColors.warning, chartBorderColors.secondary, chartBorderColors.lightBlue, chartBorderColors.info];
                } else {
                    backgroundColor = chartType === 'line' ? chartColors.primary : Object.values(chartColors);
                    borderColor = chartType === 'line' ? chartBorderColors.primary : Object.values(chartBorderColors);
                }
                window[canvasId + 'Instance'] = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: datasetLabel, data: data, backgroundColor: backgroundColor,
                            borderColor: borderColor, borderWidth: 1,
                            tension: chartType === 'line' ? 0.1 : undefined,
                            fill: chartType === 'line' ? false : undefined,
                        }]
                    },
                    options: chartOptions
                });
                return window[canvasId + 'Instance'];
            }
            let dateLabels = @json($dateLabels ?? []);
            let ticketSalesData = @json($ticketSalesData ?? []);
            let userRegistrationData = @json($userRegistrationData ?? []);
            let popularTicketLabels = @json($popularTicketLabels ?? []);
            let popularTicketData = @json($popularTicketData ?? []);

            initChart('ticketSalesChart', 'line', dateLabels, ticketSalesData, 'Liczba sprzedanych biletów');
            initChart('userRegistrationsChart', 'bar', dateLabels, userRegistrationData, 'Liczba nowych użytkowników');
            initChart('popularTicketTypesChart', 'bar', popularTicketLabels, popularTicketData, 'Liczba zakupów', { indexAxis: 'y' });

            const statisticsFilterForm = document.getElementById('statisticsFilterForm');
            if (statisticsFilterForm) {
                const startDateInput = document.getElementById('filter_start_date');
                const endDateInput = document.getElementById('filter_end_date');

                function setDateOrderValidity() {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    const endDateFeedbackDiv = endDateInput.nextElementSibling;

                    endDateInput.setCustomValidity("");
                    if (endDateFeedbackDiv && !endDateInput.classList.contains('is-invalid-from-backend')) {
                        const backendErrorMessage = "{{ $errors->first('filter_end_date') }}";
                        if (!backendErrorMessage) {
                            endDateFeedbackDiv.textContent = 'Data końcowa jest wymagana.';
                        }
                    }

                    if (startDate && endDate) {
                        const dStartDate = new Date(startDate);
                        const dEndDate = new Date(endDate);

                        if (dEndDate < dStartDate) {
                            const orderErrorMessage = 'Data końcowa nie może być wcześniejsza niż data początkowa.';
                            endDateInput.setCustomValidity(orderErrorMessage);
                            if (endDateFeedbackDiv) {
                                const backendErrorMessage = "{{ $errors->first('filter_end_date') }}";
                                if (!backendErrorMessage) {
                                    endDateFeedbackDiv.textContent = orderErrorMessage;
                                }
                            }
                        } else {
                            endDateInput.setCustomValidity("");
                            if (endDateFeedbackDiv && endDateFeedbackDiv.textContent === 'Data końcowa nie może być wcześniejsza niż data początkowa.') {
                                const backendErrorMessage = "{{ $errors->first('filter_end_date') }}";
                                if (!backendErrorMessage) {
                                    endDateFeedbackDiv.textContent = 'Data końcowa jest wymagana.';
                                }
                            }
                        }
                    }
                }

                startDateInput.addEventListener('input', setDateOrderValidity);
                endDateInput.addEventListener('input', setDateOrderValidity);

                setDateOrderValidity();
            }
        });
    </script>
@endpush
