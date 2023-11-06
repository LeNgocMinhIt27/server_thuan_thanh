<?php

namespace App\Http\Controllers\Api\QuenMatKhau;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Crypt;
use DB;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Str;


class QuenMatKhauController extends Controller
{
    public function postEmail(Request $request)
    {
        $created_at = Carbon::now()->toDateTimeString();
        $limitedTime = Carbon::parse($created_at)->addHours(24)->toDateTimeString();

        $hashedPassword = Hash::make($request->username);
        $userExists = DB::table('password_resets')
            ->where('email', $request->username)
            ->exists();

        if ($userExists) {
            DB::table('password_resets')->where('email', $request->username)
                ->update(['token' => $hashedPassword, 'created_at' => $created_at]);
        } else {

            DB::table('password_resets')->insert([
                'email' => $request->username,
                'token' => $hashedPassword,
                'created_at' => $created_at
            ]);
        }

        $username = $request->username;
        $resetLink = config('app.url') . '/doi-mat-khau?token=' . $hashedPassword;
        Mail::send([], [], function ($message) use ($username, $resetLink) {
            $message->to($username)->subject('Đổi mật khẩu');
            $message->setBody(view('emails.check_email_forget', ['resetLink' => $resetLink, 'username' => $username])->render(), 'text/html');
        });
    }

    public function checkToken(Request $request)
    {
        $currentTime = Carbon::now()->toDateTimeString();

        $data = DB::table('password_resets')
            ->where('token', $request['token'])
            ->first();

        if ($data) {
            $created_at = $data->created_at;
            $limitedTime = Carbon::parse($created_at)->addHours(24)->toDateTimeString();
            if ($currentTime < $limitedTime) {
                return response()->json(['message' =>  'True'], 200);
            } else {
                return response()->json(['message' =>  'False'], 400);
            }
        } else {
            return response()->json(['message' =>  'False'], 400);
        }
    }

    public function resetPassword(Request $request)
    {

        $data = DB::table('password_resets')
            ->where('token', $request['token'])
            ->first();

        if (!$data) {
            return response()->json(['message' =>  'Đường dẫn đã quá hạn. Vui lòng tạo đường dẫn mới để thiết lập mật khẩu mới'], 400);
        }
        if (Hash::check($data->email, $request['token'])) {
            $limitedTime = Carbon::parse($data->created_at)->addHours(24)->toDateTimeString();
            $currentTime = Carbon::now()->toDateTimeString();

            if ($currentTime < $limitedTime) {
                $hashedPassword = Hash::make($request->password);
                DB::table('users')
                    ->where('username', $data->email)
                    ->update(['password' => $hashedPassword]);

                DB::table('password_resets')
                    ->where('email', $data->email)
                    ->delete();

                return response()->json(['message' =>  'Đổi mật khẩu thành công'], 200);
            } else {
                DB::table('password_resets')
                    ->where('email', $data->email)
                    ->delete();

                return response()->json(['message' =>  'Đường dẫn đã quá hạn. Vui lòng tạo đường dẫn mới để thiết lập mật khẩu mới'], 400);
            }
        } else {
            return response()->json(['message' =>  'Đường dẫn đã quá hạn. Vui lòng tạo đường dẫn mới để thiết lập mật khẩu mới'], 400);
        }
    }
}
