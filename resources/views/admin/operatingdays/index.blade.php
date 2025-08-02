<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Zarządzanie dniami kursowania</h5>
        <a href="{{ route('admin.operatingdays.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Dodaj dzień kursowania
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
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                @foreach($operatingDays as $day)
                    <tr>
                        <td>{{ $day->day_id }}</td>
                        <td>{{ $day->name }}</td>
                        <td>{{ $day->description ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.operatingdays.edit', $day->day_id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                            <form action="{{ route('admin.operatingdays.destroy', $day->day_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć ten dzień kursowania?')">Usuń</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $operatingDays->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
