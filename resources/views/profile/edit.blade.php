@extends('layouts.app')

@section('page_title', 'Edytuj profil')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">Edytuj profil</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                                <input class="form-control @error('profile_photo') is-invalid @enderror" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg">
                                <small class="form-text text-muted">Maksymalny rozmiar 2MB. Dozwolone formaty: JPG, PNG.</small>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Imię i nazwisko</label>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus maxlength="255">
                                <div class="invalid-feedback">Imię i nazwisko jest wymagane.</div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Adres email</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" maxlength="255">
                                <div class="invalid-feedback">Podaj poprawny adres email.</div>
                            </div>
                            <hr>
                            <h5 class="mb-3">Zmień hasło (opcjonalnie)</h5>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Obecne hasło</label>
                                <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" autocomplete="current-password">
                                <small class="form-text text-muted">Wypełnij, jeśli chcesz zmienić hasło.</small>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Nowe hasło</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password" minlength="8">
                                <div class="invalid-feedback">Nowe hasło musi mieć co najmniej 8 znaków.</div>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Potwierdź nowe hasło</label>
                                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" autocomplete="new-password" minlength="8">
                                <div class="invalid-feedback">Potwierdzenie hasła musi mieć co najmniej 8 znaków.</div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Zapisz zmiany
                                </button>
                                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary mt-2">Anuluj</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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
                    @if(!$user->profile_photo_path)
                        imagePreview.style.display = 'none';
                    if(iconPreview) iconPreview.style.display = 'block';
                    @else
                        imagePreview.src = "{{ asset('storage/' . $user->profile_photo_path) }}";
                    imagePreview.style.display = 'block';
                    if(iconPreview) iconPreview.style.display = 'none';
                    @endif
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->has('current_password') || $errors->has('password'))
            const currentPasswordInput = document.getElementById('current_password');
            if (currentPasswordInput) {
                currentPasswordInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            @endif
            const profilePhotoInput = document.getElementById('profile_photo');
            if (profilePhotoInput) {
                profilePhotoInput.addEventListener('change', previewImage);
            }
        });
    </script>
@endpush
