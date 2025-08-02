<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ticketId = $this->route('ticket') ? $this->route('ticket')->ticket_id : null;

        return [
            'ticket_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tickets', 'ticket_name')->ignore($ticketId, 'ticket_id'),
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'validity_hours' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
