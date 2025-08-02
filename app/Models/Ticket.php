<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';
    protected $fillable = ['ticket_name', 'description', 'price', 'validity_hours', 'is_active'];

    public function purchasedTickets()
    {
        return $this->hasMany(PurchasedTicket::class, 'ticket_id', 'ticket_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($ticket) {
            if ($ticket->purchasedTickets()->exists()) {
                throw new \Exception("Nie można usunąć typu biletu, ponieważ istnieją zakupione bilety tego typu.");
            }
        });
    }
}
