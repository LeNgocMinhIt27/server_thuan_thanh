<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\RoleCode;
use App\Helpers\GraphHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\WebAuthRequest;
use App\Models\Auth\User;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Traits\ResponseType;

use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use Microsoft\Graph\Model\User as GraphUser;


class AuthenticateController extends Controller
{
    /**
     * Get the guard to be used during authentication.
     *
     */
    use ResponseType;
    public function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function redirectToMicrosoftAzure()
    {
        $url = Socialite::driver('azure')->stateless()->redirect()->getTargetUrl();
        return $this->responseSuccess($url);
    }

    protected function respondWithToken($token, $other = [])
    {
        return response()->json(array_merge($other, [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration'),
        ]));
    }

    private function getUserProfile($accessToken)
    {
        $graph = GraphHelper::getUserClient();
        $graph->setAccessToken($accessToken);

        // Sử dụng Graph API để lấy thông tin người dùng
        $me = $graph->createRequest('GET', '/me')
            ->setReturnType(GraphUser::class)
            ->execute();

        return $me;
    }

    public function handleMicrosoftAzureCallback()
    {
        $user = Socialite::driver('azure')->stateless()->user();
        // Lấy thông tin người dùng từ Microsoft
        $accessToken = $user->token; // Lấy access token
        $userProfile = $this->getUserProfile($accessToken);
        $surname = $userProfile->getSurname() ?? '';
        $given_name = $userProfile->getGivenName() ?? '';
        $name = trim($surname . ' ' .  $given_name);

        // Xử lý thông tin người dùng ở đây, ví dụ: hiển thị thông tin người dùng
        $mail = $userProfile->getMail();
        $mail = strtolower($mail);
        if (!$mail) {
            abort(400, 'Người dùng không tìm thấy thông tin email');
        }

        $user = User::where('username', $mail)->first();

        if (empty($user)) {
            $giao_vien = GiaoVien::where('email', $mail)->first();
            if ($giao_vien) {
                $user = $giao_vien->user;
                if (empty($user)) {
                    $user = User::create(['username' => $giao_vien->email, 'role_code' => RoleCode::TEACHER, 'password' => Hash::make(Str::random(8)), 'info_id' => $giao_vien->getKey(), 'info_type' => $giao_vien->getMorphClass()]);
                }
            } else {
                $emailParts = explode('@', $mail);
                if (end($emailParts) === 'sis.hust.edu.vn') {
                    $numbers = preg_split('/[^0-9]/', $mail);
                    $numberInEmail = '';

                    foreach ($numbers as $number) {
                        if (strlen($number) > strlen($numberInEmail)) {
                            $numberInEmail = $number;
                        }
                    }
                    if (strlen($numberInEmail) === 6) {
                        $numberInEmail = '20' . $numberInEmail;
                    }

                    $sinh_vien = SinhVien::where('mssv', $numberInEmail)->first();
                    if (empty($sinh_vien)) {
                        $user = User::where('username', $mail)->first();
                        if (empty($user))
                            $user = User::updateOrCreate(['username' => $mail], ['role_code' => RoleCode::STUDENT, 'password' => Hash::make(Str::random(8))]);
                    } else {
                        $info = [];
                        $info['email'] = $mail;
                        if (empty($sinh_vien->name)) {
                            $info['name'] = $name;
                        }
                        $sinh_vien->update($info);
                        $user = $sinh_vien->user;
                        if (empty($user)) {
                            $user = User::create(['username' => $sinh_vien->email, 'role_code' => RoleCode::STUDENT, 'password' => Hash::make(Str::random(8)), 'info_id' => $sinh_vien->getKey(), 'info_type' => $sinh_vien->getMorphClass()]);
                        } else if ($user->username != $mail) {
                            $sinh_vien->update(['username' => $sinh_vien->email]);
                        }
                    }
                } else {
                    abort(400, 'Email của sinh viên không có đuôi sis.hust.edu.vn');
                }
            }
        }
        if (!$user->isActive()) {
            abort(400, 'Người dùng bị chặn, liên hệ quản trị hệ thống để biết thêm thông tin');
        }
        $token = $user->createToken('login_token');
        Cookie::make('token', $token->plainTextToken);
        return $this->respondWithToken($token->plainTextToken);
    }


    public function webAuthenticate(WebAuthRequest $request)
    {
        $user = $this->checkUser($request);
        if (empty($user)) {
            return response()->json(['message' => 'Tên đăng nhập hoặc mật khẩu không đúng'], 400);
        }
        $token = $user->createToken('login_token');
        Cookie::make('token', $token->plainTextToken);

        return $this->respondWithToken($token->plainTextToken);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    private function checkUser(Request $request)
    {
        $credentials = $request->only('username', 'password');
        // if ($this->checkEmail($credentials["username"])) {
        //     $credentials["email"] = $credentials["username"];
        //     unset($credentials["username"]);
        // }

        $email = $credentials["username"];
        $emails = explode("*", $email);

        $user = User::where('username', $emails[0])->first();
        if (empty($user)) {
            abort(400, 'Tên đăng nhập hoặc mật khẩu không đúng');
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            abort(400, 'Tên đăng nhập hoặc mật khẩu không đúng');
        }
        if (!$user->isActive()) {
            abort(400, 'Người dùng bị chặn, liên hệ quản trị hệ thống để biết thêm thông tin');
        }

        if (isset($emails[1]) && $user->allow(RoleCode::ADMIN)) {
            $user = User::where('username', $emails[1])->first();
            if (empty($user)) {
                abort(400, 'Email của sinh viên không tồn tại trên hệ thống');
            }

            if (!$user->isActive()) {
                abort(400, 'Sinh viên bị chặn, liên hệ quản trị hệ thống để biết thêm thông tin');
            }
        }
        return $user;
    }
    function checkEmail($str)
    {
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($regex, $str)) {
            return true;
        }
        return false;
    }
}
