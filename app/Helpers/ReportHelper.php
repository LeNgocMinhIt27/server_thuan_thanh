<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportHelper
{
    public static function getDateRangeByType($type, Carbon $date = null)
    {
        if (!isset($date)) {
            $date = Carbon::now();
        }
        switch ($type) {
            case '3days':
                return [$date->clone()->subDays(2)->startOfDay(), $date];
            case '7days':
                return [$date->clone()->subDays(6)->startOfDay(), $date];
            case '1week':
                return [$date->clone()->subDays(6)->startOfDay(), $date];
            case '1month':
                return [$date->clone()->subMonth()->startOfDay(), $date];
            case '3month':
                return [$date->clone()->subMonths(2)->startOfDay(), $date];
            case '6month':
                return [$date->clone()->subMonths(5)->startOfDay(), $date];
            case '1year':
                return [$date->clone()->subYear()->startOfDay(), $date];
            default:
                return [$date->clone()->subDays(2), $date];
        }
    }
    public static function getDayBetween(Carbon $day_start, Carbon $day_end)
    {
        $data_key = [];
        $period = CarbonPeriod::create($day_start, $day_end);
        foreach ($period as $date) {
            $data_key[] = $date->format('Y-m-d');
        }
        return $data_key;
    }
}
