<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait TimeStatisticalsHelper
{
    private function _getDayBetween(Carbon $day_start, Carbon $day_end)
    {
        $data_key = [];
        $period = CarbonPeriod::create($day_start, $day_end);
        foreach ($period as $date) {
            $data_key[] = $date->format('d-m-Y');
        }
        return $data_key;
    }
    private function _getMonthBetween(Carbon $day_start, Carbon $day_end)
    {
        $data_key = [];
        foreach (CarbonPeriod::create($day_start, '1 month', $day_end) as $month) {
            $data_key[] = $month->format('m-Y');
        }
        return $data_key;
    }
    private function getDataKeyByPgSQL(string $unit, string $field_time = 'updated_at')
    {
        $format_date = '';
        $data_key = [];
        switch ($unit) {
            case 'day':
                $format_date = "TO_CHAR(DATE(" . $field_time . "),'DD-MM-YYYY')";
                break;
            case 'week':
                $format_date = "concat(LPAD(extract (week from " . $field_time . ")::CHAR(2), 2, '0'),'-',extract (year from " . $field_time . "))";
                break;
            case 'month':
                $format_date = "TO_CHAR(DATE(" . $field_time . "),'MM-YYYY')";
                break;
            default:
                $format_date = "Date(" . $field_time . ")";

                break;
        }
        return ['data_key' => $data_key, 'format_date' => $format_date];
    }
    private function getDataKeyByMySQL(string $unit, string $field_time = 'updated_at')
    {
        $format_date = '';
        $data_key = [];
        switch ($unit) {
            case 'day':
                $format_date = "date_format(DATE(" . $field_time . "),'%d-%m-%Y')";
                break;
            case 'week':
                $format_date = "concat(LPAD(extract (week from " . $field_time . ")::CHAR(2), 2, '0'),'-',extract (year from " . $field_time . "))";
                break;
            case 'month':
                $format_date = "date_format(DATE(" . $field_time . "),'%m-%Y')";
                break;
            default:
                $format_date = "Date(" . $field_time . ")";
                break;
        }
        return ['data_key' => $data_key, 'format_date' => $format_date];
    }
    private function getTypeHanderCustom($date_start, $date_end, string $unit = 'day', string $field_time = 'updated_at')
    {
        $typeHandle = ["unit" => $unit];
        $start = $date_start;
        $end = $date_end;
        $data = ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
        $type_db = config('database.default');
        switch ($type_db) {
            case 'pgsql':
                $data_db = $this->getDataKeyByPgSQL($unit, $field_time);
                break;
            case 'mysql':
                $data_db = $this->getDataKeyByMySQL($unit, $field_time);
                break;
            default:
                abort(500, 'Not support dabase:' . $type_db);
                break;
        }
        $typeHandle['formatDate'] = $data_db['format_date'];
        $data['data_key'] = $data_db['data_key'];
        $typeHandle['data'] = $data;
        return $typeHandle;
    }
    private function _getTypeTimeStatisticalData(string $type, string $fieldTime = 'updated_at', string $default_unit = null)
    {
        $typeHandles = [
            '3days' => [
                'unit' => 'day',
                'data' => function () {
                    $end = Carbon::now()->addDay(1);
                    $start = Carbon::now()->subDay(1);
                    return ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
                },
            ],
            '7days' => [
                'unit' => 'day',
                'data' => function () {
                    $end = Carbon::now()->addDay(1);
                    $start = Carbon::now()->subDay(5);
                    return ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
                },
            ],
            '1months' => [
                'unit' => 'day',
                'data' => function () {
                    $end = Carbon::now()->addMonths(1);
                    $start = Carbon::now();
                    return ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
                },
            ],
            '3months' => [
                'unit' => 'month',
                'data' => function () {
                    $end = Carbon::now()->addMonths(1);
                    $start = Carbon::now()->subMonths(2);
                    return ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
                },
            ],
            '6months' => [
                'unit' => 'month',
                'data' => function () {
                    $end = Carbon::now()->addMonths(1);
                    $start = Carbon::now()->subMonths(5);
                    return ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
                },
            ],
            '1years' => [
                'unit' => 'month',
                'data' => function () {
                    $end = Carbon::now()->addMonths(1);
                    $start = Carbon::now()->subMonths(11);
                    return ['day_start' => $start->startOfDay(), 'day_end' => $end->endOfDay()];
                },
            ],
            'all' => [
                'unit' => 'month',
                'data' => function () {
                    return ['data_key' => [], 'day_start' => null, 'day_end' => null];
                },
            ],
        ];
        $typeHandle = $typeHandles[$type];
        if (!isset($typeHandle)) {
            return null;
        }
        if (isset($default_unit)) {
            $typeHandle['unit'] = $default_unit;
        }
        $data = $typeHandle['data']();
        $end = $data['day_end'];
        $start = $data['day_start'];

        $unit = $typeHandle['unit'];

        $data_key = [];

        switch ($unit) {
            case 'day':
                $formatDate = "date_format(DATE(" . $fieldTime . "),'DD-MM-YYYY')";
                if (isset($start) && isset($end)) {
                    $data_key = $this->_getDayBetween($start, $end);
                }
                break;
            case 'week':
                $formatDate = "concat(LPAD(extract (week from " . $fieldTime . ")::CHAR(2), 2, '0'),'-',extract (year from " . $fieldTime . "))";
                break;
            case 'month':
                $formatDate = "date_format(DATE(" . $fieldTime . "),'MM-YYYY')";
                if (isset($start) && isset($end)) {
                    $data_key = $this->_getMonthBetween($start, $end);
                }
                break;
            default:
                $formatDate = "Date(" . $fieldTime . ")";
                if (isset($start) && isset($end)) {
                    $data_key = $this->_getDayBetween($start, $end);
                }

                break;
        }
        $typeHandle['formatDate'] = $formatDate;

        $data['data_key'] = $data_key;
        $typeHandle['data'] = $data;

        return $typeHandle;
    }
    public function handleGetDataTimeStatistical($typeHandle, Builder $query, array $data_default = [], string $field_time = 'updated_at')
    {
        if (!isset($typeHandle)) {
            abort(400, 'type not support');
        }
        $data = $typeHandle['data'];
        $data_key = $data['data_key'];
        $date_start = $data['day_start'];
        $date_end = $data['day_end'];
        $formatDate = $typeHandle['formatDate'];
        if (isset($date_start) && isset($date_end)) {
            $query->whereBetween($field_time, [$date_start, $date_end]);
        }
        $data_group = $query->addSelect(DB::raw($formatDate . " as date"))->orderBy('date')->groupBy('date')->get()->groupBy('date');
        $data_return = [];

        $length = count($data_key);
        if ($length == 0) {
            $data_return = $data_group->flatten(2);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $key = $data_key[$i];
                if (isset($data_group[$key])) {
                    $data_return[] = $data_group[$key][0];
                } else {
                    $data_return[] = array_merge(['date' => $key], $data_default);
                }
            }
        }
        return $data_return;
    }
    //
    public function handlerTimeStatistical(string $type, callable $cb, array $data_default = [], string $field_time = 'updated_at')
    {
        $typeHandle = $this->_getTypeTimeStatisticalData($type);
        if (!isset($typeHandle)) {
            abort(400, 'type not support');
        }
        $unit = $typeHandle['unit'];
        $data = $typeHandle['data'];
        $data_key = $data['data_key'];
        $length = count($data_key);
        for ($i = 0; $i < $length; $i++) {
            $key = $data_key[$i];
            $data = $cb($key, $typeHandle);
            $data_return[] = array_merge(['date' => $key], $data, $data_default);
        }
        return $data_return;
    }
}
