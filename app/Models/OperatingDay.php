<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingDay extends Model
{
    protected $table = 'operating_days';
    protected $primaryKey = 'day_id';

    protected $fillable = [
        'name',
        'description'
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class, 'day_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($operatingDay) {
            if ($operatingDay->trips()->exists()) {
                throw new \Exception("Nie można usunąć dnia kursowania, ponieważ istnieją przypisane do niego rozkłady jazdy (kursy).");
            }
        });
    }
}
