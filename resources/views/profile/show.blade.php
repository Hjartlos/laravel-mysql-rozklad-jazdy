@extends('layouts.app')

@section('page_title', 'Profil')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card auto-height-card">
                    <div class="card-header">Profil użytkownika</div>

                    <div class="card-body auto-height">
                        <div class="text-center mb-4">
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Zdjęcie profilowe" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <i class="fas fa-user-circle fa-7x text-secondary"></i>
                            @endif
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label fw-bold">Imię i nazwisko:</label>
                            <div class="col-sm-9">
                                <p class="form-control-plaintext">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label fw-bold">Adres email:</label>
                            <div class="col-sm-9">
                                <p class="form-control-plaintext">{{ $user->email }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edytuj profil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
