<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'user_id',
        'from_stop_id',
        'to_stop_id',
        'line_id',
        'departure_time',
        'arrival_time',
        'duration_minutes',
        'price',
        'status',
        'payment_id',
        'route_data'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'route_data' => 'array',
        'price' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function fromStop()
    {
        return $this->belongsTo(Stop::class, 'from_stop_id', 'stop_id');
    }

    public function toStop()
    {
        return $this->belongsTo(Stop::class, 'to_stop_id', 'stop_id');
    }

    public function line()
    {
        return $this->belongsTo(Line::class, 'line_id', 'line_id');
    }

    public function purchasedTickets()
    {
        return $this->hasMany(PurchasedTicket::class, 'transaction_id', 'transaction_id');
    }
}
