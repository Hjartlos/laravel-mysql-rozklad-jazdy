<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stop extends Model
{
    use HasFactory;

    protected $primaryKey = 'stop_id';

    protected $fillable = [
        'stop_name',
        'location_lat',
        'location_lon'
    ];

    public function lines()
    {
        return $this->belongsToMany(Line::class, 'linestop', 'stop_id', 'line_id')
            ->withPivot('sequence');
    }

    public function departureTimes()
    {
        return $this->hasMany(DepartureTime::class, 'stop_id');
    }

    public function transactionsFrom()
    {
        return $this->hasMany(Transaction::class, 'from_stop_id', 'stop_id');
    }

    public function transactionsTo()
    {
        return $this->hasMany(Transaction::class, 'to_stop_id', 'stop_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($stop) {
            if ($stop->transactionsFrom()->exists() || $stop->transactionsTo()->exists()) {
                throw new \Exception("Nie można usunąć przystanku, ponieważ istnieją powiązane z nim transakcje.");
            }
            DB::table('linestop')->where('stop_id', $stop->stop_id)->delete();
            $stop->departureTimes()->delete();
        });
    }
}
