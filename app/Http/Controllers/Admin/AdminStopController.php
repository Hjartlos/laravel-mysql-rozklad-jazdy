<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStopRequest;
use App\Http\Requests\UpdateStopRequest;
use App\Models\Stop;

class AdminStopController extends Controller
{
    public function index()
    {
        return view('admin.stops.index');
    }

    public function create()
    {
        return view('admin.stops.create');
    }

    public function store(StoreStopRequest $request)
    {
        try {
            Stop::create($request->validated());
            return redirect()->route('dashboard', ['tab' => 'stops'])
                ->with('success', 'Przystanek został dodany.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error', 'Przystanek o tej nazwie już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function edit(Stop $stop)
    {
        return view('admin.stops.edit', ['stop' => $stop]);
    }

    public function update(UpdateStopRequest $request, Stop $stop)
    {
        try {
            $stop->update($request->validated());
            return redirect()->route('dashboard', ['tab' => 'stops'])
                ->with('success', 'Przystanek został zaktualizowany.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error', 'Przystanek o tej nazwie już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function destroy(Stop $stop)
    {
        try {
            $stop->delete();
            return redirect()->route('dashboard', ['tab' => 'stops'])
                ->with('success', 'Przystanek został usunięty.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard', ['tab' => 'stops'])
                ->with('error', $e->getMessage());
        }
    }
}
