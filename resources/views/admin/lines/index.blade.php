
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Zarządzanie liniami</h5>
            <a href="{{ route('admin.lines.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Dodaj linię
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Numer linii</th>
                        <th>Nazwa linii</th>
                        <th>Kierunek</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($lines as $line)
                        <tr>
                            <td>{{ $line->line_number }}</td>
                            <td>{{ $line->line_name }}</td>
                            <td>{{ $line->direction ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.lines.edit', $line->line_id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                                <form action="{{ route('admin.lines.destroy', $line->line_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć tę linię?')">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $lines->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
