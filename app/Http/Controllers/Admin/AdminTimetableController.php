<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Models\Stop;
use App\Models\Trip;
use App\Models\Line;
use App\Models\DepartureTime;
use App\Models\OperatingDay;

class AdminTimetableController extends Controller
{
    public function index()
    {
        return view('admin.timetable.index');
    }

    public function create()
    {
        $lines = Line::orderBy('line_number')->get();
        $operatingDays = OperatingDay::all();
        $stops = Stop::orderBy('stop_name')->get();
        return view('admin.timetable.create', ['lines' => $lines, 'operatingDays' => $operatingDays, 'stops' => $stops]);
    }

    public function store(StoreTimetableRequest $request)
    {
        $validated = $request->validated();

        try {
            \DB::beginTransaction();

            $trip = Trip::create([
                'line_id' => $validated['line_id'],
                'day_id' => $validated['day_id']
            ]);

            foreach ($validated['times'] as $time) {
                DepartureTime::create([
                    'trip_id' => $trip->trip_id,
                    'stop_id' => $time['stop_id'],
                    'departure_time' => $time['departure_time']
                ]);
            }

            \DB::commit();
            return redirect()->route('dashboard', ['tab' => 'timetable'])
                ->with('success', 'Rozkład jazdy został dodany.');
        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error',
                    'Wykryto zduplikowane czasy odjazdów. Ten przystanek ma już przypisany czas odjazdu dla tego kursu.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function edit(Trip $trip)
    {
        $lines = Line::orderBy('line_number')->get();
        $operatingDays = OperatingDay::all();
        $stops = Stop::orderBy('stop_name')->get();
        $departureTimes = DepartureTime::where('trip_id', $trip->trip_id)
            ->with('stop')
            ->orderBy('departure_time')
            ->get();

        return view('admin.timetable.edit', ['trip' => $trip, 'lines' => $lines, 'operatingDays' => $operatingDays, 'departureTimes' => $departureTimes, 'stops' => $stops]);
    }

    public function update(UpdateTimetableRequest $request, Trip $trip)
    {
        $validated = $request->validated();

        try {
            \DB::beginTransaction();

            $trip->update([
                'line_id' => $validated['line_id'],
                'day_id' => $validated['day_id']
            ]);

            $timeIds = collect($validated['times'])->pluck('time_id')->filter()->toArray();
            DepartureTime::where('trip_id', $trip->trip_id)
                ->whereNotIn('time_id', $timeIds)
                ->delete();

            foreach ($validated['times'] as $time) {
                if (!empty($time['time_id'])) {
                    DepartureTime::where('time_id', $time['time_id'])->update([
                        'stop_id' => $time['stop_id'],
                        'departure_time' => $time['departure_time']
                    ]);
                } else {
                    DepartureTime::create([
                        'trip_id' => $trip->trip_id,
                        'stop_id' => $time['stop_id'],
                        'departure_time' => $time['departure_time']
                    ]);
                }
            }

            \DB::commit();
            return redirect()->route('dashboard', ['tab' => 'timetable'])
                ->with('success', 'Rozkład jazdy został zaktualizowany.');
        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error',
                    'Wykryto zduplikowane czasy odjazdów. Ten przystanek ma już przypisany czas odjazdu dla tego kursu.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();
        return redirect()->route('dashboard', ['tab' => 'timetable'])
            ->with('success', 'Rozkład jazdy został usunięty.');
    }

    public function getLineStops($lineId)
    {
        $line = Line::findOrFail($lineId);
        $stops = $line->stops()->orderBy('linestop.sequence')->get();
        return response()->json($stops);
    }
}
