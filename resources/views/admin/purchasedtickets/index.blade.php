<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Zakupione bilety</h5>
        <a href="{{ route('admin.purchasedtickets.create') }}" class="btn btn-primary btn-sm">
            Dodaj zakupiony bilet
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID zakupu</th>
                    <th>Użytkownik</th>
                    <th>Typ biletu</th>
                    <th>ID Transakcji</th>
                    <th>Ważny od</th>
                    <th>Ważny do</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                @forelse($purchasedTickets as $purchasedTicket)
                    <tr>
                        <td>{{ $purchasedTicket->purchase_id }}</td>
                        <td>{{ $purchasedTicket->user->name ?? 'N/A' }}</td>
                        <td>{{ $purchasedTicket->ticket->ticket_name ?? 'N/A' }}</td>
                        <td>
                            @if($purchasedTicket->transaction)
                                <a href="{{ route('admin.transactions.show', $purchasedTicket->transaction_id) }}">{{ $purchasedTicket->transaction_id }}</a>
                            @else
                                {{ $purchasedTicket->transaction_id ?? 'N/A' }}
                            @endif
                        </td>
                        <td>{{ $purchasedTicket->valid_from->format('d.m.Y H:i') }}</td>
                        <td>{{ $purchasedTicket->valid_until->format('d.m.Y H:i') }}</td>
                        <td>
                            @php
                                $statusClass = 'secondary';
                                $statusText = ucfirst($purchasedTicket->status);
                                if ($purchasedTicket->status === 'aktywny') {
                                    if ($purchasedTicket->valid_until > now()) {
                                        $statusClass = 'success';
                                        $statusText = 'Aktywny';
                                    } else {
                                        $statusClass = 'danger';
                                        $statusText = 'Wygasły';
                                    }
                                } elseif ($purchasedTicket->status === 'oczekujący') {
                                    $statusClass = 'warning';
                                    $statusText = 'Oczekujący';
                                } elseif ($purchasedTicket->status === 'anulowany') {
                                    $statusClass = 'dark';
                                    $statusText = 'Anulowany';
                                } elseif ($purchasedTicket->status === 'wygasły') {
                                    $statusClass = 'danger';
                                    $statusText = 'Wygasły';
                                }
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.purchasedtickets.show', $purchasedTicket->purchase_id) }}" class="btn btn-sm btn-info" title="Szczegóły">Szczegóły</a>
                            <a href="{{ route('admin.purchasedtickets.edit', $purchasedTicket->purchase_id) }}" class="btn btn-sm btn-warning" title="Edytuj">Edytuj</a>
                            <form action="{{ route('admin.purchasedtickets.destroy', $purchasedTicket->purchase_id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć ten zakupiony bilet? Może to wpłynąć na integralność danych transakcji.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Usuń">Usuń</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Brak zakupionych biletów.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $purchasedTickets->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
