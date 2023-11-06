<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HustHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetKiHienGioController extends Controller
{
    public function kiHoc()
    {
        $now = Carbon::now();
        return $this->responseSuccess(HustHelper::getKiHoc($now));
    }
    public function index()
    {
        $now = Carbon::now();
        return $this->responseSuccess([
            'tuan' => HustHelper::getWeek($now),
            'tuan_ki_hoc' => HustHelper::getWeekKiHoc($now),
            'ki_hoc' => HustHelper::getKiHoc($now)
        ]);
    }
}
