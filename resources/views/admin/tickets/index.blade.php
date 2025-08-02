<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Zarządzanie biletami</h5>
        <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Dodaj bilet
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nazwa</th>
                    <th>Opis</th>
                    <th>Cena</th>
                    <th>Ważność (godz.)</th>
                    <th>Aktywny</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->ticket_id }}</td>
                        <td>{{ $ticket->ticket_name }}</td>
                        <td>{{ Str::limit($ticket->description, 50) }}</td>
                        <td>{{ number_format($ticket->price, 2) }} zł</td>
                        <td>{{ $ticket->validity_hours }}</td>
                        <td>
                                <span class="badge bg-{{ $ticket->is_active ? 'success' : 'danger' }}">
                                    {{ $ticket->is_active ? 'Tak' : 'Nie' }}
                                </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.tickets.edit', $ticket->ticket_id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                            <form action="{{ route('admin.tickets.destroy', $ticket->ticket_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć ten bilet?')">Usuń</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $tickets->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
