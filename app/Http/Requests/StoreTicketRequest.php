<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_name' => 'required|string|max:255|unique:tickets,ticket_name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'validity_hours' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
