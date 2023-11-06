<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\Regex;
use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Models\Auth\User;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use \App\Http\Resources\User as UserResource;
use App\Traits\ResponseType;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Storage;

/**
 * Class ChangePasswordRequest.
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|min:8|confirmed',
        ];
    }
}
class ProfileController extends Controller
{
    use ResponseType;
    /**
     * Get the guard to be used during authentication.
     *
     */
    public function guard()
    {
        return Auth::guard();
    }
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json(['user' => new \App\Http\Resources\Profile(User::with('info')->find($user->id)->append(['is_sinh_vien', 'is_giao_vien']))]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $atts = $request->only('old_password', 'password');
        $user = $request->user();
        if (Hash::check($atts['old_password'], $user->password)) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        } else {
            return response()->json([
                'status_code' => 422,
                'errors' => ['old_password' => ['Mật khẩu nhập không khớp với mật khẩu hiện tại của bạn.']],
                'message' => '422 Unprocessable Entity',
            ], 422);
        }

        return response()->json([
            'status_code' => 200,
            'message' => 'Changed password successful!',
        ]);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();
        $info = $request->all();
        $request->validate([]);
        $disk = Storage::disk('local');
        // $user = $request->user();
        if ($request->has('avatar')) {
            $file = $request->file('avatar');
            if ($disk->exists($user->avatar)) {
                $disk->delete($user->avatar);
            }
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = $fileName . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($disk->path('public/images/avatar/'), $fileName);
            $user->avatar_url = '/storage/images/avatar/' . $fileName;
        }
        if (isset($info['email'])) {
            $user->update(['username' => $info['email']]);
        }
        if (isset($user->info)) {
            $user->info->update($info);
        } else if ($user->role_code == RoleCode::STUDENT) {
            $user->info()->create($info);
        }

        try {
            $user->save();
        } catch (\Exception $e) {
            throw $e;
        }
        return response()->json([
            'status_code' => 200,
            'message' => 'Updated Profile',
            'data' => new \App\Http\Resources\Profile($user->load('info'))
        ]);
    }
    function checkEmail($str)
    {
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($regex, $str)) {
            return true;
        }
        return false;
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->syncRole('access:user');

        event(new Registered($user));

        return $this->responseSuccess([], 'User Registered');
    }

    public function checkToken()
    {
        // Middleware auth:sanctum will handle logic for this method

        return response()->json([
            'isValid' => true
        ]);
    }
}
