<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stop_name' => 'required|string|max:255|unique:stops,stop_name',
            'location_lat' => 'required|numeric',
            'location_lon' => 'required|numeric',
        ];
    }
}
