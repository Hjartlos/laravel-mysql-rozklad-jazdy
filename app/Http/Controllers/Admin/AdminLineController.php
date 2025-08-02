<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLineRequest;
use App\Http\Requests\UpdateLineRequest;
use App\Models\Line;
use App\Models\Stop;
use Illuminate\Support\Facades\DB;

class AdminLineController extends Controller
{
    public function index()
    {
        return view('admin.lines.index');
    }

    public function create()
    {
        $stops = Stop::orderBy('stop_name')->get();
        $oldStopsData = [];

        if (session()->hasOldInput('stops')) {
            foreach (session()->getOldInput('stops') as $index => $stopData) {
                if (isset($stopData['stop_id'])) {
                    $stopModel = Stop::find($stopData['stop_id']);
                    if ($stopModel) {
                        $oldStopsData[] = [
                            'stop_id' => $stopModel->stop_id,
                            'stop_name' => $stopModel->stop_name,
                            'sequence' => $stopData['sequence'] ?? ($index + 1),
                        ];
                    }
                }
            }
        }

        return view('admin.lines.create', [
            'stops' => $stops,
            'oldStopsData' => $oldStopsData
        ]);
    }

    public function store(StoreLineRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $line = Line::create([
                'line_number' => $validated['line_number'],
                'line_name' => $validated['line_name'],
                'direction' => $validated['direction'] ?? null
            ]);

            if (isset($validated['stops']) && is_array($validated['stops'])) {
                foreach ($validated['stops'] as $stop) {
                    if (isset($stop['stop_id']) && isset($stop['sequence'])) {
                        $line->stops()->attach($stop['stop_id'], ['sequence' => $stop['sequence']]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('dashboard', ['tab' => 'lines'])
                ->with('success', 'Linia została utworzona pomyślnie.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error',
                    'Linia o numerze ' . ($validated['line_number'] ?? '') . ' w kierunku ' . ($validated['direction'] ?? '') . ' już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd bazy danych: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Wystąpił błąd podczas tworzenia linii: ' . $e->getMessage());
        }
    }

    public function edit(Line $line)
    {
        $allAvailableStops = Stop::orderBy('stop_name')->get();
        $currentStopsDataForView = collect();

        if (session()->has('lineStops')) {
            $currentStopsDataForView = session('lineStops');
        } elseif (session()->hasOldInput('stops')) {
            $oldStopsInput = session()->getOldInput('stops');
            $currentStopsDataForView = collect($oldStopsInput)->map(function($item, $index) {
                if (isset($item['stop_id'])) {
                    $stopModel = Stop::find($item['stop_id']);
                    return $stopModel ? (object)[
                        'stop_id' => $item['stop_id'],
                        'stop_name' => $stopModel->stop_name,
                        'pivot' => (object)['sequence' => $item['sequence'] ?? ($index + 1)]
                    ] : null;
                }
                return null;
            })->filter()->values();
        } else {
            $currentStopsDataForView = $line->stops()->orderBy('pivot_sequence')->get()->map(function($stop) {
                return (object)[
                    'stop_id' => $stop->stop_id,
                    'stop_name' => $stop->stop_name,
                    'pivot' => (object)['sequence' => $stop->pivot->sequence]
                ];
            });
        }

        return view('admin.lines.edit', [
            'line' => $line,
            'stops' => $allAvailableStops,
            'currentStopsData' => $currentStopsDataForView
        ]);
    }

    public function update(UpdateLineRequest $request, Line $line)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $line->update([
                'line_number' => $validated['line_number'],
                'line_name' => $validated['line_name'],
                'direction' => $validated['direction'] ?? null
            ]);

            $line->stops()->detach();

            if (isset($validated['stops']) && is_array($validated['stops'])) {
                foreach ($validated['stops'] as $stop) {
                    if (isset($stop['stop_id']) && isset($stop['sequence'])) {
                        $line->stops()->attach($stop['stop_id'], ['sequence' => $stop['sequence']]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('dashboard', ['tab' => 'lines'])
                ->with('success', 'Linia została zaktualizowana pomyślnie.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $stopsDataToFlash = collect();
            $inputStops = $request->input('stops', $request->old('stops', []));
            if (!empty($inputStops)) {
                foreach ($inputStops as $index => $stopInput) {
                    if (isset($stopInput['stop_id'])) {
                        $stopModel = Stop::find($stopInput['stop_id']);
                        if ($stopModel) {
                            $stopsDataToFlash->push((object)[
                                'stop_id' => $stopModel->stop_id,
                                'stop_name' => $stopModel->stop_name,
                                'pivot' => (object)['sequence' => $stopInput['sequence'] ?? ($index + 1)]
                            ]);
                        }
                    }
                }
            }
            if ($stopsDataToFlash->isEmpty()) {
                $stopsDataToFlash = $line->stops()->orderBy('pivot_sequence')->get()->map(function($s) {
                    return (object)['stop_id' => $s->stop_id, 'stop_name' => $s->stop_name, 'pivot' => (object)['sequence' => $s->pivot->sequence]];
                });
            }

            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('lineStops', $stopsDataToFlash)->with('unique_error',
                    'Linia o numerze ' . ($validated['line_number'] ?? '') . ' w kierunku ' . ($validated['direction'] ?? '') . ' już istnieje.');
            }
            return back()->withInput()->with('lineStops', $stopsDataToFlash)->with('error', 'Wystąpił błąd bazy danych: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            $stopsDataToFlash = collect();
            $inputStops = $request->input('stops', $request->old('stops', []));

            if (!empty($inputStops)) {
                foreach ($inputStops as $index => $stopInput) {
                    if (isset($stopInput['stop_id'])) {
                        $stopModel = Stop::find($stopInput['stop_id']);
                        if ($stopModel) {
                            $stopsDataToFlash->push((object)[
                                'stop_id' => $stopModel->stop_id,
                                'stop_name' => $stopModel->stop_name,
                                'pivot' => (object)['sequence' => $stopInput['sequence'] ?? ($index + 1)]
                            ]);
                        }
                    }
                }
            } else {
                $stopsDataToFlash = $line->stops()->orderBy('pivot_sequence')->get()->map(function($s) {
                    return (object)['stop_id' => $s->stop_id, 'stop_name' => $s->stop_name, 'pivot' => (object)['sequence' => $s->pivot->sequence]];
                });
            }

            return back()
                ->withInput()
                ->with('lineStops', $stopsDataToFlash)
                ->with('error', 'Wystąpił błąd podczas aktualizacji linii: ' . $e->getMessage());
        }
    }

    public function destroy(Line $line)
    {
        DB::beginTransaction();
        try {
            $line->stops()->detach();
            $line->delete();
            DB::commit();
            return redirect()->route('dashboard', ['tab' => 'lines'])
                ->with('success', 'Linia została usunięta pomyślnie.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Wystąpił błąd podczas usuwania linii: ' . $e->getMessage());
        }
    }

    public function stops(Line $line)
    {
        $stopsData = $line->stops()->orderBy('pivot_sequence')->get()->map(function($stop) {
            return [
                'stop_id' => $stop->stop_id,
                'stop_name' => $stop->stop_name,
                'sequence' => $stop->pivot->sequence,
                'latitude' => $stop->latitude,
                'longitude' => $stop->longitude,
            ];
        });
        return response()->json($stopsData);
    }
}
