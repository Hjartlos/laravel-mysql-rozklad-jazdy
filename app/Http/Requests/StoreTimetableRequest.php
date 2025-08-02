<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimetableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'line_id' => 'required|exists:lines,line_id',
            'day_id' => 'required|exists:operating_days,day_id',
            'times' => 'required|array',
            'times.*.stop_id' => 'required|exists:stops,stop_id',
            'times.*.departure_time' => 'required|date_format:H:i'
        ];
    }
}
