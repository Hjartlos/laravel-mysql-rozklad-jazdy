<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketCalculationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'from_stop_id' => 'required|exists:stops,stop_id',
            'to_stop_id' => 'required|exists:stops,stop_id',
            'line_id' => 'required|exists:lines,line_id',
            'ticket_id' => 'required|exists:tickets,ticket_id',
        ];
    }
}
