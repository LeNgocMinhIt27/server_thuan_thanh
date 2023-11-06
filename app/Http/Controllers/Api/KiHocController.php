<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KiHoc;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\QueryBuilder;

class KiHocController extends Controller
{
    public function index()
    {
        return response()->json(KiHoc::select('name')->orderBy('name', 'desc')->get()->pluck('name'));
    }
}
