<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperatingDayRequest;
use App\Http\Requests\UpdateOperatingDayRequest;
use App\Models\OperatingDay;

class AdminOperatingDayController extends Controller
{
    public function index()
    {
        return view('admin.operatingdays.index');
    }

    public function create()
    {
        return view('admin.operatingdays.create');
    }

    public function store(StoreOperatingDayRequest $request)
    {
        try {
            OperatingDay::create($request->validated());
            return redirect()->route('dashboard', ['tab' => 'operatingdays'])
                ->with('success', 'Dzień kursowania został dodany pomyślnie.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error', 'Dzień kursowania o tej nazwie już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function edit(OperatingDay $operatingDay)
    {
        return view('admin.operatingdays.edit', ['operatingDay' => $operatingDay]);
    }

    public function update(UpdateOperatingDayRequest $request, OperatingDay $operatingDay)
    {
        try {
            $operatingDay->update($request->validated());
            return redirect()->route('dashboard', ['tab' => 'operatingdays'])
                ->with('success', 'Dzień kursowania został zaktualizowany pomyślnie.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error', 'Dzień kursowania o tej nazwie już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function destroy(OperatingDay $operatingDay)
    {
        try {
            $operatingDay->delete();
            return redirect()->route('dashboard', ['tab' => 'operatingdays'])
                ->with('success', 'Dzień kursowania został usunięty pomyślnie.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard', ['tab' => 'operatingdays'])
                ->with('error', $e->getMessage());
        }
    }
}
