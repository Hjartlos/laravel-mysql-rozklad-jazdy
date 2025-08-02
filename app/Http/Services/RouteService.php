<?php

namespace App\Http\Services;

use App\Models\DepartureTime;
use App\Models\Line;
use App\Models\LineStop;
use App\Models\Stop;
use Carbon\Carbon;

class RouteService
{
    protected $departureService;

    protected $travelTimeBetweenStops = [
        1 => 3,
        2 => 4,
        3 => 3,
    ];

    public function __construct(DepartureService $departureService)
    {
        $this->departureService = $departureService;
    }

    public function findRoutes($fromStopId, $toStopId, Carbon $departureTime, $isNextDay = false)
    {
        $routes = [];
        $fromStop = Stop::find($fromStopId);
        $toStop = Stop::find($toStopId);

        if (!$fromStop || !$toStop) {
            return $routes;
        }

        $commonLines = $this->findCommonLines($fromStopId, $toStopId);

        if ($commonLines->isEmpty()) {
            return $routes;
        }

        $searchDate = $isNextDay ? (clone $departureTime)->addDay() : clone $departureTime;
        $dayId = $this->departureService->getDayTypeId($searchDate);

        $searchTime = $isNextDay ? '00:00:00' : $departureTime->format('H:i:s');

        foreach ($commonLines as $line) {
            $fromStopSequence = $this->getStopSequence($line->line_id, $fromStopId);
            $toStopSequence = $this->getStopSequence($line->line_id, $toStopId);

            if ($fromStopSequence >= $toStopSequence) {
                continue;
            }

            $departures = DepartureTime::where('stop_id', $fromStopId)
                ->whereHas('trip', function($q) use ($line, $dayId) {
                    $q->where('line_id', $line->line_id)
                        ->where('day_id', $dayId);
                })
                ->where('departure_time', '>=', $searchTime)
                ->orderBy('departure_time')
                ->with('trip')
                ->limit(1)
                ->get();

            foreach ($departures as $departure) {
                $departureTime = Carbon::parse($departure->departure_time);

                $stopsDifference = $toStopSequence - $fromStopSequence;

                $travelTimeMinutes = $stopsDifference * ($this->travelTimeBetweenStops[$line->line_id] ?? 3);

                $arrivalTime = (clone $departureTime)->addMinutes($travelTimeMinutes);

                $intermediateStops = LineStop::where('line_id', $line->line_id)
                    ->where('sequence', '>=', $fromStopSequence)
                    ->where('sequence', '<=', $toStopSequence)
                    ->orderBy('sequence')
                    ->with('stop')
                    ->get();

                $stopIds = $intermediateStops->pluck('stop_id');
                $departureTimes = DepartureTime::whereIn('stop_id', $stopIds)
                    ->where('trip_id', $departure->trip_id)
                    ->get()
                    ->keyBy('stop_id');

                $allStops = [];
                foreach ($intermediateStops as $i => $stop) {
                    $isStart = $i === 0;
                    $isEnd = $i === $intermediateStops->count() - 1;

                    $stopTime = (clone $departureTime)->addMinutes(
                        ($stop->sequence - $fromStopSequence) * ($this->travelTimeBetweenStops[$line->line_id] ?? 3)
                    );

                    $allStops[] = [
                        'stop_id' => $stop->stop->stop_id,
                        'stop_name' => $stop->stop->stop_name,
                        'sequence' => $stop->sequence,
                        'location_lat' => $stop->stop->location_lat,
                        'location_lon' => $stop->stop->location_lon,
                        'is_start' => $isStart,
                        'is_end' => $isEnd,
                        'departure_time' => $stopTime->format('H:i')
                    ];
                }

                $routes[] = [
                    'line_id' => $line->line_id,
                    'line_number' => $line->line_number,
                    'line_name' => $line->line_name,
                    'from_stop' => $fromStop->stop_name,
                    'to_stop' => $toStop->stop_name,
                    'departure_time' => $departureTime->format('H:i'),
                    'arrival_time' => $arrivalTime->format('H:i'),
                    'duration' => $travelTimeMinutes,
                    'all_stops' => $allStops,
                    'next_day' => $isNextDay
                ];
            }
        }

        usort($routes, function($a, $b) {
            return strtotime($a['departure_time']) - strtotime($b['departure_time']);
        });

        return $routes;
    }

    private function findCommonLines($fromStopId, $toStopId)
    {
        $fromStopLines = LineStop::where('stop_id', $fromStopId)->pluck('line_id');
        $toStopLines = LineStop::where('stop_id', $toStopId)->pluck('line_id');

        $commonLineIds = $fromStopLines->intersect($toStopLines);

        return Line::whereIn('line_id', $commonLineIds)->get();
    }

    private function getStopSequence($lineId, $stopId)
    {
        $lineStop = LineStop::where('line_id', $lineId)
            ->where('stop_id', $stopId)
            ->first();

        return $lineStop ? $lineStop->sequence : 9999;
    }
}
