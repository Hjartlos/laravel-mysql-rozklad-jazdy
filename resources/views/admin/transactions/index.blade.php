<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Transakcje</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID transakcji</th>
                    <th>Użytkownik</th>
                    <th>Kwota</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Trasa/Linia</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_id }}</td>
                        <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                        <td>{{ number_format($transaction->price, 2) }} zł</td>
                        <td>
                            @php
                                $statusClass = match(strtolower($transaction->status)) {
                                    'zakończona' => 'success',
                                    'oczekująca' => 'warning',
                                    'anulowana' => 'secondary',
                                    'wygasła' => 'dark',
                                    'nieudana' => 'danger',
                                    default => 'info',
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td>{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            @if($transaction->fromStop && $transaction->toStop)
                                {{ $transaction->fromStop->stop_name }} → {{ $transaction->toStop->stop_name }}
                                @if($transaction->line)
                                    (Linia {{ $transaction->line->line_number }})
                                @endif
                            @elseif($transaction->line)
                                Linia {{ $transaction->line->line_number }}
                            @else
                                Brak danych trasy
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.transactions.show', $transaction->transaction_id) }}" class="btn btn-sm btn-info">Szczegóły</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
