<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stopId = $this->route('stop') ? $this->route('stop')->stop_id : null;

        return [
            'stop_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stops', 'stop_name')->ignore($stopId, 'stop_id'),
            ],
            'location_lat' => 'required|numeric',
            'location_lon' => 'required|numeric',
        ];
    }
}
