<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin')
        ]);

        return redirect()->route('dashboard', ['tab' => 'users'])
            ->with('success', 'Użytkownik został dodany.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if (Auth::id() !== $user->user_id) {
            if ($request->has('is_admin')) {
                $user->is_admin = $request->boolean('is_admin');
            }
        } else {
            if ($request->has('is_admin') && $user->is_admin !== $request->boolean('is_admin')) {
                return back()->withInput()->with('warning', 'Nie możesz zmienić własnego statusu administratora.');
            }
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $successMessage = 'Profil użytkownika ' . $user->name . ' został zaktualizowany.';

        if (isset($validatedData['password']) && $validatedData['password']) {
            $user->password = Hash::make($validatedData['password']);
            $successMessage .= ' Hasło zostało również zmienione.';
        }

        $user->save();

        return redirect()->route('dashboard', ['tab' => 'users'])
            ->with('success', $successMessage);
    }

    public function destroy(User $user)
    {
        if ($user->user_id === auth()->id()) {
            return redirect()->route('dashboard', ['tab' => 'users'])
                ->with('error', 'Nie możesz usunąć własnego konta.');
        }
        $user->delete();
        return redirect()->route('dashboard', ['tab' => 'users'])
            ->with('success', 'Użytkownik został usunięty.');
    }
}
