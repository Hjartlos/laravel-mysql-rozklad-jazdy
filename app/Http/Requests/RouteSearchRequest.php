<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => 'required|exists:stops,stop_id',
            'to' => 'required|exists:stops,stop_id|different:from',
            'departure_time' => 'required|date_format:Y-m-d\TH:i'
        ];
    }
}
