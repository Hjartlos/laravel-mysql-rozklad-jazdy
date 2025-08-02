    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Zarządzanie rozkładami jazdy</h5>
            <a href="{{ route('admin.timetable.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Dodaj rozkład jazdy
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Linia</th>
                        <th>Dzień kursowania</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($trips as $trip)
                        <tr>
                            <td>{{ $trip->line->line_number }} ({{ $trip->line->line_name }})</td>
                            <td>{{ $trip->operatingDay->name }}</td>
                            <td>
                                <a href="{{ route('admin.timetable.edit', $trip->trip_id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                                <form action="{{ route('admin.timetable.destroy', $trip->trip_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć ten rozkład?')">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $trips->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
