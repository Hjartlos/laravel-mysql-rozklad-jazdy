<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartureTime extends Model
{
    protected $table = 'departure_times';
    protected $primaryKey = 'time_id';

    protected $fillable = [
        'trip_id',
        'stop_id',
        'departure_time'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function stop()
    {
        return $this->belongsTo(Stop::class, 'stop_id');
    }
}
