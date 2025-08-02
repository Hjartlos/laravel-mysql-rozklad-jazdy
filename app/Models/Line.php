<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Line extends Model
{
    use HasFactory;

    protected $primaryKey = 'line_id';

    protected $fillable = [
        'line_number',
        'line_name',
        'direction'
    ];

    public function stops()
    {
        return $this->belongsToMany(Stop::class, 'linestop', 'line_id', 'stop_id')
            ->withPivot('sequence')
            ->orderBy('linestop.sequence');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'line_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($line) {
            DB::table('linestop')->where('line_id', $line->line_id)->delete();

            $line->trips()->each(function ($trip) {
                $trip->delete();
            });
        });
    }
}
