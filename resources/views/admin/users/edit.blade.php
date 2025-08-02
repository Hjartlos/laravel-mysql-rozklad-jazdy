@extends('layouts.app')

@section('page_title', 'Edycja użytkownika: ' . $user->name)

@section('content')
    <div class="container">
        <h1>Edytuj użytkownika</h1>
        <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="text-center mb-4">
                @if($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Zdjęcie profilowe" id="profileImagePreview" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <i class="fas fa-user-circle fa-7x text-secondary" id="profileIconPreview"></i>
                    <img src="#" alt="Podgląd zdjęcia" id="profileImagePreview" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover; display: none;">
                @endif
            </div>
            <div class="mb-3">
                <label for="profile_photo" class="form-label">Zdjęcie profilowe (opcjonalnie)</label>
                <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg">
                <small class="form-text text-muted">Maksymalny rozmiar 2MB. Dozwolone formaty: JPG, PNG.</small>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Imię i nazwisko</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
                <div class="invalid-feedback">Imię i nazwisko jest wymagane.</div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Adres e-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                <div class="invalid-feedback">Podaj poprawny adres email.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Nowe hasło (pozostaw puste, aby nie zmieniać)</label>
                <input type="password" class="form-control" id="password" name="password" minlength="8">
                <small class="text-muted">Minimum 8 znaków. Wypełnij tylko jeśli chcesz zmienić hasło.</small>
                <div class="invalid-feedback">Hasło musi mieć co najmniej 8 znaków.</div>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Potwierdzenie nowego hasła</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8">
                <div class="invalid-feedback">Potwierdzenie hasła musi mieć co najmniej 8 znaków.</div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_admin" value="0">
                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" value="1"
                    {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_admin">Administrator</label>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success">Zapisz zmiany</button>
                <a href="{{ route('dashboard', ['tab' => 'users']) }}" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            const imagePreview = document.getElementById('profileImagePreview');
            const iconPreview = document.getElementById('profileIconPreview');
            reader.onload = function(){
                if (imagePreview) {
                    imagePreview.src = reader.result;
                    imagePreview.style.display = 'block';
                }
                if (iconPreview) {
                    iconPreview.style.display = 'none';
                }
            }
            if (event.target.files && event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            } else {
                if (imagePreview) {
                    @if($user && !$user->profile_photo_path)
                        imagePreview.style.display = 'none';
                    if(iconPreview) iconPreview.style.display = 'block';
                    @elseif($user && $user->profile_photo_path)
                        imagePreview.src = "{{ $user ? asset('storage/' . $user->profile_photo_path) : '#' }}";
                    imagePreview.style.display = 'block';
                    if(iconPreview) iconPreview.style.display = 'none';
                    @else
                        imagePreview.style.display = 'none';
                    if(iconPreview) iconPreview.style.display = 'block';
                    @endif
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const profilePhotoInput = document.getElementById('profile_photo');
            if (profilePhotoInput) {
                profilePhotoInput.addEventListener('change', previewImage);
            }
        });
    </script>
@endpush
