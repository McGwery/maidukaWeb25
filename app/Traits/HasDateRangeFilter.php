<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;

trait HasDateRangeFilter
{
    /**
     * Get date range based on filter type
     *
     * @param Request $request
     * @return array ['startDate' => Carbon, 'endDate' => Carbon]
     */
    protected function getDateRange(Request $request): array
    {
        $filter = $request->input('dateFilter', 'today');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        return match ($filter) {
            'today' => [
                'startDate' => Carbon::today()->startOfDay(),
                'endDate' => Carbon::today()->endOfDay(),
            ],
            'this_week' => [
                'startDate' => Carbon::now()->startOfWeek(),
                'endDate' => Carbon::now()->endOfWeek(),
            ],
            'this_month' => [
                'startDate' => Carbon::now()->startOfMonth(),
                'endDate' => Carbon::now()->endOfMonth(),
            ],
            'custom' => [
                'startDate' => $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay(),
                'endDate' => $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay(),
            ],
            default => [
                'startDate' => Carbon::today()->startOfDay(),
                'endDate' => Carbon::today()->endOfDay(),
            ],
        };
    }

    /**
     * Validate date filter request
     *
     * @param Request $request
     * @return array
     */
    protected function validateDateFilter(Request $request): array
    {
        return $request->validate([
            'dateFilter' => 'nullable|in:today,this_week,this_month,custom',
            'startDate' => 'nullable|required_if:dateFilter,custom|date',
            'endDate' => 'nullable|required_if:dateFilter,custom|date|after_or_equal:startDate',
        ]);
    }
}

