<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketSuccessRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ticket_id' => 'required|exists:tickets,ticket_id',
            'session_id' => 'required|string'
        ];
    }
}
