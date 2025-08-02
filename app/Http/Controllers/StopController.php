<?php

namespace App\Http\Controllers;

use App\Http\Services\DepartureService;
use App\Models\Stop;
use App\Models\LineStop;
use App\Models\DepartureTime;
use App\Models\Line;
use Carbon\Carbon;

class StopController extends Controller
{
    private $departureService;

    public function __construct(DepartureService $departureService)
    {
        $this->departureService = $departureService;
    }

    public function index()
    {
        $stops = Stop::orderBy('stop_name')
            ->select('stop_id', 'stop_name', 'location_lat', 'location_lon')
            ->get();

        return view('stops.index', [
            'stops' => $stops
        ]);
    }

    public function show($stopId)
    {
        $stop = Stop::with([
            'lines' => function ($query) {
                $query->orderBy('line_number');
            }
        ])->findOrFail($stopId);

        $selectedLineId = request('line_id');
        $lineStops = null;
        $departureTimes = collect();
        $nextDeparture = null;

        $currentDayId = $this->departureService->getDayTypeId(Carbon::now());
        $activeTabId = $currentDayId;

        if ($selectedLineId) {
            $lineStops = LineStop::where('line_id', $selectedLineId)
                ->orderBy('sequence')
                ->with('stop')
                ->get()
                ->map(function($lineStop) use ($stopId) {
                    return [
                        'stop_id' => $lineStop->stop->stop_id,
                        'stop_name' => $lineStop->stop->stop_name,
                        'location_lat' => $lineStop->stop->location_lat,
                        'location_lon' => $lineStop->stop->location_lon,
                        'sequence' => $lineStop->sequence,
                        'is_current' => $lineStop->stop->stop_id == $stopId
                    ];
                });

            $nextDepartureResult = $this->departureService->findNextDeparture($stopId, $selectedLineId);
            if ($nextDepartureResult) {
                $nextDeparture = $nextDepartureResult['departure'];
                $activeTabId = $nextDepartureResult['day_id'];
            }

            $departureTimes = DepartureTime::where('stop_id', $stopId)
                ->whereHas('trip', function($query) use ($selectedLineId) {
                    $query->where('line_id', $selectedLineId);
                })
                ->with(['trip.operatingDay' => function($query) {
                    $query->orderBy('day_id');
                }])
                ->orderBy('departure_time')
                ->get()
                ->groupBy(function($item) {
                    return $item->trip->operatingDay->day_id;
                })
                ->sortKeys();
        }

        $stopLines = $stop->lines->map(function($line) {
            return [
                'line_id' => $line->line_id,
                'line_number' => $line->line_number,
                'line_name' => $line->line_name
            ];
        });

        return view('stops.show', [
            'stop' => $stop,
            'lineStops' => $lineStops,
            'selectedLine' => $selectedLineId ? Line::find($selectedLineId) : null,
            'stopLines' => $stopLines,
            'departureTimes' => $departureTimes,
            'nextDeparture' => $nextDeparture,
            'activeTabId' => $activeTabId,
        ]);
    }
}
