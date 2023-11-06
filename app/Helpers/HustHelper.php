<?php

namespace App\Helpers;

use Carbon\Carbon;

class HustHelper
{

    public static function getKiHoc(Carbon $now)
    {
        $year = $now->year;
        $month = $now->month;
        $year -= 1;
        $ki = '3';
        if ($month < 8) {
            $ki = 2;
        }
        if ($month > 9) {
            $year += 1;
            $ki = 1;
        }
        return "$year$ki";
    }
    public static function getWeek(Carbon $now)
    {
        // t2 của tuần t1;
        $day_start_week_1 = SettingHelper::getConfig('config.day_start_week_1')->setting_value;
        $t2_tuan_1 = new Carbon($day_start_week_1);
        $week_1 = $t2_tuan_1->week();
        $week_current = $now->week();
        $week_hust_current = $week_current -  $week_1 + 1;
        if (!$t2_tuan_1->isSameYear($now)) {
            $week_cuoi_nam_truoc = (new Carbon())->endOfYear()->week();
            $week_hust_current += $week_cuoi_nam_truoc - $week_1 + 1;
        }
        return $week_hust_current;
    }
    public static function getWeekKiHoc(Carbon $now)
    {
        // t2 của tuần t1;
        $day_start_week_1 = SettingHelper::getConfig('config.day_start_week_1_ki_hoc')->setting_value;
        $t2_tuan_1 = new Carbon($day_start_week_1);
        $week_1 = $t2_tuan_1->week();
        $week_current = $now->week();
        $week_hust_current = $week_current -  $week_1 + 1;
        if (!$t2_tuan_1->isSameYear($now)) {
            $week_cuoi_nam_truoc = (new Carbon())->endOfYear()->week();
            $week_hust_current += $week_cuoi_nam_truoc - $week_1 + 1;
        }
        return $week_hust_current;
    }
    public static function geT2AtWeek($week): Carbon
    {
        // t2 của tuần t1;
        $day_start_week_1 = SettingHelper::getConfig('config.day_start_week_1')->setting_value;
        $t2_tuan_1 = new Carbon($day_start_week_1);
        $t2_tuan_1->addWeeks($week - 1);
        return $t2_tuan_1;
    }
}
