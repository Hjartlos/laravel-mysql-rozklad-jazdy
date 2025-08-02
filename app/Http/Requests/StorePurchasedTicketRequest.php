<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchasedTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|exists:tickets,ticket_id',
            'user_id' => 'required|exists:users,user_id',
            'transaction_id' => 'required|integer|exists:transactions,transaction_id|unique:purchased_tickets,transaction_id',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'status' => ['required', 'string', Rule::in(['aktywny', 'oczekujący', 'anulowany', 'wygasły'])],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_id.unique' => 'Dla tej transakcji istnieje już zakupiony bilet.',
            'valid_until.after_or_equal' => 'Data "Ważny do" nie może być wcześniejsza niż data "Ważny od".',
            'status.in' => 'Wybrany status jest nieprawidłowy. Dozwolone statusy to: aktywny, oczekujący, anulowany, wygasły.',
        ];
    }
}
