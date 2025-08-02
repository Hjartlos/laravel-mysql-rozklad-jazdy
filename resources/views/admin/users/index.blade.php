    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Zarządzanie użytkownikami</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Dodaj użytkownika
            </a>
        </div>
        <div class="card-body">
            <div class="alert alert-info small mb-3" role="alert">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Uwaga:</strong> Usunięcie użytkownika spowoduje, że w jego dotychczasowych transakcjach pole <code>user_id</code> zostanie automatycznie ustawione na <code>NULL</code> (oznaczając transakcję jako anonimową pod względem użytkownika).
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imie</th>
                        <th>E-mail</th>
                        <th>Rola</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->user_id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->is_admin ? 'Administrator' : 'Użytkownik' }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-sm btn-warning">Edytuj</a>
                                <form action="{{ route('admin.users.destroy', $user->user_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika? Pamiętaj, że jego transakcje zostaną oznaczone jako anonimowe.')">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->appends(['tab' => $activeTab])->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
