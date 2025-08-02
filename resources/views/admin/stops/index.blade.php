    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Zarządzanie przystankami</h5>
            <a href="{{ route('admin.stops.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Dodaj przystanek
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Lokalizacja</th>
                        <th>Liczba linii</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stops as $stop)
                        <tr>
                            <td>{{ $stop->stop_name }}</td>
                            <td>{{ $stop->location_lat }}, {{ $stop->location_lon }}</td>
                            <td><span class="badge bg-secondary">{{ $stop->lines_count }}</span></td>
                            <td>
                                <a href="{{ route('admin.stops.edit', $stop->stop_id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                                <form action="{{ route('admin.stops.destroy', $stop->stop_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć ten przystanek?')">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $stops->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
