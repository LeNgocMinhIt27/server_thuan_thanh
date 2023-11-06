<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\User\SinhVien;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterDateRange;
use App\Models\User\GiaoVien;
use DateTime;

class SinhVienController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function sinhVienFilter(Request $request)
    {
        $query = SinhVien::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('name')
            ->allowedSearch(['name', 'mssv'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function indexAgGrid(Request $request)
    {
        $query = SinhVien::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['name', 'mssv', 'email'])
            ->allowedAgGrid([])
            ->defaultSort('name')
            ->allowedIncludes(['user'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function indexForLopThi(Request $request, $id)
    {
        $query = SinhVien::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['name', 'mssv', 'email'])
            ->allowedAgGrid([])
            ->defaultSort('name')
            ->allowedIncludes(['user'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mssv' => 'required|string|size:8',
            'email' => 'nullable|string|max:255|email:rfc',
        ], [], [
            'name' => __('sinh-vien.field.name'),
            'mssv' => __('sinh-vien.field.mssv'),
            'email' => __('sinh-vien.field.email'),
        ]);
        $info = $request->all();
        if ($request->has('email')) {
            $email = $info['email'];
            $giao_vien = GiaoVien::query()
                ->where('email', $email)
                ->first();

            if ($giao_vien) {
                abort(400, 'Email của sinh viên trùng với email của giảng viên');
            }
        }

        if (isset($info['birthday'])) {
            $birthday = new DateTime($info['birthday']);
            $info['birthday'] = $birthday->format('Y-m-d');
        }
        $sinh_vien = SinhVien::create($info);
        return $this->responseSuccess($sinh_vien);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mssv' => 'required|string|size:8',
            'email' => 'nullable|string|max:255|email:rfc',
        ], [], [
            'name' => __('sinh-vien.field.name'),
            'mssv' => __('sinh-vien.field.name'),
            'email' => __('sinh-vien.field.email'),
        ]);
        $info = $request->all();
        $email = $info['email'];

        $giao_vien = GiaoVien::query()
            ->where('email', $email)
            ->first();

        if ($giao_vien) {
            abort(400, 'Email của sinh viên trùng với email của giảng viên');
        }

        if (isset($info['birthday'])) {
            $birthday = new DateTime($info['birthday']);
            $info['birthday'] = $birthday->format('Y-m-d');
        }
        $sinh_vien = SinhVien::findOrFail($id);
        $result = $sinh_vien->update($info);
        return $this->responseSuccess($result);
    }

    public function destroy($id)
    {
        $sinh_vien = SinhVien::findOrFail($id);
        $result = $sinh_vien->delete();
        return $this->responseSuccess($result);
    }

    public function listSinhVienMany(Request $request, $id)
    {
        $query = SinhVien::query()->whereIn('id', explode(',', $id));
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('name')
            ->allowedSearch(['name', 'mssv'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
}
