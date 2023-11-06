<?php

namespace App\Http\Controllers\Api\System;

use App\Constants\Regex;
use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ResetUserPasswordRequest;
use App\Models\Auth\User;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UserUpdateAvatarRequest;
use App\Http\Requests\User\UserUpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Traits\ResponseType;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterDateRange;
use App\Library\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Hash;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use Storage;

class UserController extends Controller
{
    use ResponseType;

    public function index(Request $request)
    {
        $query = User::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['username'])->allowedSorts([
                'username',
                'inactive',
            ])
            ->allowedFilters([
                'username',
                'inactive',
                AllowedFilter::custom('date_filter', new FilterDateRange(), 'created_at'),
            ])
            ->defaultSort('created_at')
            ->allowedIncludes(['info'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function indexAgGird(Request $request)
    {
        $query = User::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['username'])
            ->allowedAgGrid([])
            ->defaultSort('created_at')
            ->allowedIncludes(['info'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function show($id)
    {
        $user = User::find($id);
        return response()->json($user, 200, []);
    }

    public function downloadTemplate()
    {
        $excelFile = public_path() . '/import/users-template.xlsx';
        if (file_exists($excelFile)) {
            return response()->download($excelFile);
        }
        return response()->json([
            'message' => 'File not found',
        ], 404, []);
    }

    public function store(UserRequest $request)
    {
        $info = $request->all();
        $info['password'] = Hash::make($info['password']);
        $info['role_code'] = join(',',  $info['roles']);
        if (in_array(RoleCode::TEACHER, $request->roles) || in_array(RoleCode::ASSISTANT, $request->roles)) {
            $giao_vien = GiaoVien::firstOrCreate([
                'email' => $request->username
            ]);
            $info['info_id'] = $giao_vien->getKey();
            $info['info_type'] = $giao_vien->getMorphClass();
        } else if (in_array(RoleCode::STUDENT, $request->roles)) {
            $info = SinhVien::firstOrCreate([
                'email' => $request->username
            ]);
            $info['info_id'] = $info->getKey();
            $info['info_type'] = $info->getMorphClass();
        }
        $user = User::create($info);
        return $this->responseSuccess($user);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $info = $request->all();
        $info['role_code'] = join(',',  $info['roles']);
        $user = User::findOrFail($id);
        $user->update($info);

        if (isset($user->info_id) && $user->is_giao_vien) {
            $giaovien = GiaoVien::findOrFail($info['info_id']);
            $result = $giaovien->update([
                'email' => $request->username,
            ]);
        }
        return $this->responseSuccess();
    }

    public function updateAdmin(Request $request)
    {
        $user = $request->user();
        $info = $request->all();
        $disk = Storage::disk('local');
        if ($request->has('avatar')) {
            $file = $request->file('avatar');
            if ($disk->exists($user->avatar)) {
                $disk->delete($user->avatar);
            }
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = $fileName . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($disk->path('public/images/avatar/'), $fileName);
            $info['avatar_url'] = '/storage/images/avatar/' . $fileName;
        }
        unset($info['avatar']);
        $user->update($info);
        try {
            $user->save();
        } catch (\Exception $e) {
            throw $e;
        }
        return response()->json([
            'status_code' => 200,
            'message' => 'Updated Profile',
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (isset($user->info_id) && $user->is_giao_vien) {
            $giaovien = GiaoVien::findOrFail($user->info_id);
            $user->delete();
            $result = $giaovien->delete();
        }
        return $this->responseSuccess($result);
    }
    public function updatePassword(ResetUserPasswordRequest $request, $id = '')
    {
        $password = $request->get('password');
        $user = User::findOrFail($id);
        $password = Hash::make($password);
        $user->password = $password;
        $user->save();
        return $this->responseSuccess();
    }
    public function activeUser($id)
    {
        User::activeUser($id);
        return $this->responseSuccess();
    }
    public function inactiveUser($id)
    {
        User::inactiveUser($id);
        return $this->responseSuccess();
    }
    public function checkPassword(Request $request)
    {
        $user = $request->user();
        $checked = Hash::check($request->password, $user->password);
        return response()->json([
            'status_code' => 200,
            'data' => $checked,
        ]);
    }
}
