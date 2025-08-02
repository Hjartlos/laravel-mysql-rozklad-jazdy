<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommonLinesRequest;
use App\Http\Services\DepartureService;
use App\Models\Line;
use App\Models\LineStop;
use App\Models\DepartureTime;
use App\Models\Stop;
use Carbon\Carbon;

class LineController extends Controller
{
    private $departureService;

    public function __construct(DepartureService $departureService)
    {
        $this->departureService = $departureService;
    }

    public function index()
    {
        $lines = Line::orderBy('line_number')->get();
        $stops = Stop::select('stop_id', 'stop_name', 'location_lat', 'location_lon')->get();

        return view('lines.index', [
            'lines' => $lines,
            'stops' => $stops
        ]);
    }

    public function show($lineId)
    {
        $line = Line::findOrFail($lineId);

        $lineStops = LineStop::where('line_id', $lineId)
            ->orderBy('sequence')
            ->with('stop')
            ->get();

        $lineStopsForMap = $lineStops->map(function($ls) {
            return [
                'stop_id' => $ls->stop->stop_id,
                'stop_name' => $ls->stop->stop_name,
                'location_lat' => $ls->stop->location_lat,
                'location_lon' => $ls->stop->location_lon,
                'sequence' => $ls->sequence
            ];
        });

        $nextDeparturesByStop = $this->departureService->findNextDeparturesForLine($lineId);

        $departureTimes = collect();
        foreach ($lineStops as $lineStop) {
            $stopDepartures = DepartureTime::where('stop_id', $lineStop->stop->stop_id)
                ->whereHas('trip', function($query) use ($lineId) {
                    $query->where('line_id', $lineId);
                })
                ->with(['trip.operatingDay' => function($query) {
                    $query->orderBy('day_id');
                }])
                ->orderBy('departure_time')
                ->get()
                ->groupBy(function($item) {
                    return $item->trip->operatingDay->day_id;
                });

            $departureTimes[$lineStop->stop->stop_id] = $stopDepartures;
        }

        $allNextDepartures = collect($nextDeparturesByStop);
        $nextDeparture = $allNextDepartures->sortBy(function($departure) {
            return Carbon::parse(date('Y-m-d') . ' ' . $departure->departure_time);
        })->first();

        return view('lines.show', [
            'line' => $line,
            'lineStops' => $lineStops,
            'lineStopsForMap' => $lineStopsForMap,
            'departureTimes' => $departureTimes,
            'nextDeparture' => $nextDeparture,
            'nextDeparturesByStop' => $nextDeparturesByStop
        ]);
    }
}
