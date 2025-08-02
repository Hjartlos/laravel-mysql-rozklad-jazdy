<?php

namespace App\Http\Services;

use App\Models\Ticket;
use App\Models\LineStop;


class TicketPriceService
{
    protected $pricePerMinute = 0.10;
    protected $pricePerKilometer = 0.30;

    public function calculatePrice($durationMinutes, $distance = null, $ticketId = null)
    {
        if ($ticketId) {
            $ticket = Ticket::findOrFail($ticketId);
            $basePrice = $ticket->price;
        } else {
            $basePrice = 2.00;
        }

        $price = $basePrice;

        $price += $durationMinutes * $this->pricePerMinute;

        if ($distance) {
            $price += $distance * $this->pricePerKilometer;
        }

        return round($price, 2);
    }

    public function calculateDistance($fromLat, $fromLon, $toLat, $toLon)
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($fromLat);
        $lonFrom = deg2rad($fromLon);
        $latTo = deg2rad($toLat);
        $lonTo = deg2rad($toLon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function calculateRouteDistance($fromStopId, $toStopId, $lineId)
    {
        $fromLineStop = LineStop::where('line_id', $lineId)->where('stop_id', $fromStopId)->first();
        $toLineStop = LineStop::where('line_id', $lineId)->where('stop_id', $toStopId)->first();

        if (!$fromLineStop || !$toLineStop || $fromLineStop->sequence >= $toLineStop->sequence) {
            return 0.0;
        }

        $lineStopsOnSegment = LineStop::where('line_id', $lineId)
            ->where('sequence', '>=', $fromLineStop->sequence)
            ->where('sequence', '<=', $toLineStop->sequence)
            ->orderBy('sequence')
            ->with('stop:stop_id,location_lat,location_lon')
            ->get();

        if ($lineStopsOnSegment->count() < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;
        for ($i = 0; $i < $lineStopsOnSegment->count() - 1; $i++) {
            $currentStopLocation = $lineStopsOnSegment[$i]->stop;
            $nextStopLocation = $lineStopsOnSegment[$i + 1]->stop;

            if ($currentStopLocation && $nextStopLocation) {
                $totalDistance += $this->calculateDistance(
                    $currentStopLocation->location_lat,
                    $currentStopLocation->location_lon,
                    $nextStopLocation->location_lat,
                    $nextStopLocation->location_lon
                );
            }
        }

        return round($totalDistance, 2);
    }
}
