<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $primaryKey = 'trip_id';

    protected $fillable = [
        'line_id',
        'day_id'
    ];

    public function line()
    {
        return $this->belongsTo(Line::class, 'line_id');
    }

    public function operatingDay()
    {
        return $this->belongsTo(OperatingDay::class, 'day_id');
    }

    public function departureTimes()
    {
        return $this->hasMany(DepartureTime::class, 'trip_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($trip) {
            $trip->departureTimes()->delete();
        });
    }
}
