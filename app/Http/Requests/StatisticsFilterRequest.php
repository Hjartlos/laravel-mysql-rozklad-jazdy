<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StatisticsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter_start_date' => 'nullable|date_format:Y-m-d',
            'filter_end_date' => 'nullable|date_format:Y-m-d|after_or_equal:filter_start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'filter_start_date.date_format' => 'Data początkowa musi być w formacie RRRR-MM-DD.',
            'filter_end_date.date_format' => 'Data końcowa musi być w formacie RRRR-MM-DD.',
            'filter_end_date.after_or_equal' => 'Data końcowa nie może być wcześniejsza niż data początkowa.',
        ];
    }

    public function getFilterStartDate(): Carbon
    {
        return $this->filled('filter_start_date')
            ? Carbon::parse($this->input('filter_start_date'))->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();
    }

    public function getFilterEndDate(): Carbon
    {
        $startDate = $this->getFilterStartDate();
        $endDate = $this->filled('filter_end_date')
            ? Carbon::parse($this->input('filter_end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        return $endDate->lt($startDate) ? $startDate->copy()->endOfDay() : $endDate;
    }
}
