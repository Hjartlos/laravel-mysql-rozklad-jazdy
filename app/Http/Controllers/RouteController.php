<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommonLinesRequest;
use App\Http\Requests\RouteSearchRequest;
use App\Models\Stop;
use App\Models\Line;
use App\Models\LineStop;
use App\Http\Services\DepartureService;
use App\Http\Services\RouteService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RouteController extends Controller
{
    protected $departureService;
    protected $routeService;

    public function __construct(RouteService $routeService, DepartureService $departureService)
    {
        $this->routeService = $routeService;
        $this->departureService = $departureService;
    }

    public function index()
    {
        $stops = Stop::orderBy('stop_name')->get();
        return view('route-planner.index', [
            'stops' => $stops
        ]);
    }

    public function search(RouteSearchRequest $request)
    {
        $fromStopId = $request->input('from');
        $toStopId = $request->input('to');
        $departureTime = Carbon::parse($request->input('departure_time'));

        $routes = $this->routeService->findRoutes($fromStopId, $toStopId, $departureTime);

        if (empty($routes)) {
            $routesNextDay = $this->routeService->findRoutes($fromStopId, $toStopId, $departureTime, true);
            $routes = $routesNextDay;
        }

        $stops = Stop::orderBy('stop_name')->get();

        return view('route-planner.index', [
            'stops' => $stops,
            'routes' => $routes
        ]);
    }

    public function getCommonLines(CommonLinesRequest $request)
    {
        $fromStopId = $request->input('from_stop_id');
        $toStopId = $request->input('to_stop_id');

        if (!$fromStopId || !$toStopId) {
            return response()->json(['lines' => []]);
        }

        $fromStopLines = LineStop::where('stop_id', $fromStopId)->pluck('line_id');
        $toStopLines = LineStop::where('stop_id', $toStopId)->pluck('line_id');

        $commonLineIds = $fromStopLines->intersect($toStopLines);

        $validLines = [];
        foreach ($commonLineIds as $lineId) {
            $fromSequence = LineStop::where('line_id', $lineId)
                ->where('stop_id', $fromStopId)
                ->first()->sequence;

            $toSequence = LineStop::where('line_id', $lineId)
                ->where('stop_id', $toStopId)
                ->first()->sequence;

            if ($fromSequence < $toSequence) {
                $validLines[] = $lineId;
            }
        }

        $lines = Line::whereIn('line_id', $validLines)
            ->get();

        return response()->json(['lines' => $lines]);
    }

    public function getRouteSegmentStops(Request $request, Line $line)
    {
        $request->validate([
            'from_stop_id' => 'required|integer|exists:stops,stop_id',
            'to_stop_id'   => 'required|integer|exists:stops,stop_id',
        ]);

        $fromStopId = $request->input('from_stop_id');
        $toStopId = $request->input('to_stop_id');

        $fromLineStop = LineStop::where('line_id', $line->line_id)
            ->where('stop_id', $fromStopId)
            ->first();

        $toLineStop = LineStop::where('line_id', $line->line_id)
            ->where('stop_id', $toStopId)
            ->first();

        if (!$fromLineStop || !$toLineStop || $fromLineStop->sequence >= $toLineStop->sequence) {
            return response()->json(['error' => 'Nieprawidłowa sekwencja przystanków lub przystanki nie znajdują się na określonym odcinku linii.'], 400);
        }

        $intermediateLineStops = LineStop::where('line_id', $line->line_id)
            ->where('sequence', '>=', $fromLineStop->sequence)
            ->where('sequence', '<=', $toLineStop->sequence)
            ->orderBy('sequence')
            ->with('stop:stop_id,stop_name,location_lat,location_lon')
            ->get();

        $intermediateStopsData = $intermediateLineStops->map(function ($lineStop) {
            return [
                'stop_id'      => $lineStop->stop->stop_id,
                'stop_name'    => $lineStop->stop->stop_name,
                'location_lat' => (float) $lineStop->stop->location_lat,
                'location_lon' => (float) $lineStop->stop->location_lon,
                'sequence'     => $lineStop->sequence,
            ];
        });

        return response()->json($intermediateStopsData);
    }
}
