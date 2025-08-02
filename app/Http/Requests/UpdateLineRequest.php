<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $lineId = $this->route('line') ? $this->route('line')->line_id : null;

        return [
            'line_number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('lines')->where(function ($query) {
                    return $query->where('direction', $this->input('direction'));
                })->ignore($lineId, 'line_id')
            ],
            'line_name' => 'required|string|max:255',
            'direction' => 'nullable|string|max:255',
            'stops' => 'required|array|min:1',
            'stops.*.stop_id' => 'required|exists:stops,stop_id',
            'stops.*.sequence' => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'line_number.unique' => 'Kombinacja numeru linii i kierunku musi być unikalna.',
            'stops.min' => 'Linia musi zawierać co najmniej jeden przystanek.'
        ];
    }
}
