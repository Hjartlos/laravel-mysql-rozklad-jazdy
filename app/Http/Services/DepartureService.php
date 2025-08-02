<?php

namespace App\Http\Services;

use App\Models\DepartureTime;
use App\Models\LineStop;
use Carbon\Carbon;

class DepartureService
{
    public function findNextDeparture($stopId, $lineId = null, Carbon $currentTime = null)
    {
        if (!$currentTime) {
            $currentTime = Carbon::now();
        }

        $currentDayId = $this->getDayTypeId($currentTime);

        $query = DepartureTime::where('stop_id', $stopId);

        if ($lineId) {
            $query->whereHas('trip', function($q) use ($lineId) {
                $q->where('line_id', $lineId);
            });
        }

        $query->with(['trip.operatingDay' => function($q) {
            $q->orderBy('day_id');
        }]);

        $departures = $query->orderBy('departure_time')
            ->get()
            ->groupBy(function($item) {
                return $item->trip->operatingDay->day_id;
            });

        $todayDepartures = $departures->get($currentDayId, collect());
        $nextDeparture = $todayDepartures
            ->filter(function($departure) use ($currentTime) {
                $departureTimeToday = Carbon::parse(date('Y-m-d') . ' ' . $departure->departure_time);
                return $departureTimeToday->gt($currentTime);
            })
            ->sortBy('departure_time')
            ->first();

        if ($nextDeparture) {
            return [
                'departure' => $nextDeparture,
                'day_id' => $currentDayId
            ];
        }

        $dayOrder = $this->getNextDaysOrder($currentDayId);

        foreach ($dayOrder as $dayId) {
            $departuresForDay = $departures->get($dayId, collect());

            if ($departuresForDay->isNotEmpty()) {
                return [
                    'departure' => $departuresForDay->sortBy('departure_time')->first(),
                    'day_id' => $dayId
                ];
            }
        }

        return null;
    }

    public function getDayTypeId(Carbon $date)
    {
        return match($date->dayOfWeek) {
            0 => 3,
            6 => 2,
            default => 1,
        };
    }

    private function getNextDaysOrder($currentDayId)
    {
        $today = Carbon::now();
        $tomorrow = $today->copy()->addDay();
        $tomorrowType = $this->getDayTypeId($tomorrow);

        if ($currentDayId == 1) {
            if ($today->dayOfWeek < 5) {
                return [1, 2, 3];
            } else {
                return [2, 3, 1];
            }
        } else if ($currentDayId == 2) {
            return [3, 1, 2];
        } else {
            return [1, 2, 3];
        }
    }

    public function findNextDeparturesForLine($lineId, Carbon $currentTime = null)
    {
        if (!$currentTime) {
            $currentTime = Carbon::now();
        }

        $nextDeparturesByStop = [];

        $lineStops = LineStop::where('line_id', $lineId)
            ->orderBy('sequence')
            ->with('stop')
            ->get();

        foreach ($lineStops as $lineStop) {
            $result = $this->findNextDeparture($lineStop->stop->stop_id, $lineId, $currentTime);
            if ($result) {
                $nextDeparturesByStop[$lineStop->stop->stop_id] = $result['departure'];
            }
        }

        return $nextDeparturesByStop;
    }
}
