<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOperatingDayRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:operating_days,name,' . $this->operatingDay->day_id . ',day_id',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
