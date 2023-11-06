<?php

namespace App\Http\Controllers\Api\Diem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\RoleCode;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\DiemPhucKhao;

class DiemPhucKhaoController extends Controller
{
    public function indexAgGrid(Request $request)
    {
        $query = DiemPhucKhao::with(['lopThi', 'sinhVien', 'user']);
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['diem'])
            ->allowedIncludes(['user'])
            ->allowedAgGrid([])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
}
