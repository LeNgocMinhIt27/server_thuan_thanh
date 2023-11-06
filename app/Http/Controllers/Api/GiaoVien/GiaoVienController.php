<?php

namespace App\Http\Controllers\Api\GiaoVien;

use App\Constants\Regex;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\RoleCode;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use App\Library\QueryBuilder\Filters\Custom\FilterDateRange;
use Illuminate\Support\Facades\Hash;
use Spatie\ResponseCache\Facades\ResponseCache;

class GiaoVienController extends Controller
{
    public function index(Request $request)
    {
        $query = GiaoVien::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSorts(['name'])
            ->allowedSearch(['name'])
            ->defaultSort('name')
            ->allowedFilters([
                'name',
                AllowedFilter::custom('date_filter', new FilterDateRange(), 'created_at'),
                AllowedFilter::custom('date_filter', new FilterDateRange(), 'updated_at'),
            ])
            ->allowedIncludes(['user'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function indexAgGrid(Request $request)
    {
        $query = GiaoVien::with(['user']);
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['username'])
            ->allowedAgGrid([])
            ->allowedIncludes(['user'])
            ->defaultSort('name')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function store(Request $request)
    {
        $password = Regex::PASSWORD_REGEX;
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email:rfc|unique:users,username|unique:u_sinh_viens,email',
            'password' => "required|string|max:255|min:8|regex:$password",
            'confirm' => 'required|string|min:8',
        ], [], [
            'name' => __('giao-vien.field.name'),
            'email' => __('giao-vien.field.email'),
            'password' => __('giao-vien.field.password'),
            'confirm' => __('giao-vien.field.confirmPass'),
            'password' => Regex::PASSWORD_MESSAGE,
        ]);
        $data = $request->all();
        $giaovien = GiaoVien::create($data);
        $giaovien->user()->create([
            'username' => $request->email,
            'password' => Hash::make($request->password),
            'role_code' => RoleCode::TEACHER,
        ]);
        ResponseCache::clear();
        return $this->responseSuccess($giaovien);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email:rfc',
        ]);
        $data = $request->all();
        $giaovien = GiaoVien::findOrFail($id);
        $result = $giaovien->update($data);
        $giaovien->user()->update([
            'username' => $request->email,
        ]);
        ResponseCache::clear();
        return $this->responseSuccess($giaovien);
    }

    public function destroy($id)
    {
        $giaovien = GiaoVien::findOrFail($id);
        $result = $giaovien->delete();
        $giaovien->user()->delete();
        ResponseCache::clear();
        return $this->responseSuccess($giaovien);
    }
    public function detail($id)
    {
        $result = GiaoVien::findOrFail($id);
        return $this->responseSuccess($result);
    }
}
