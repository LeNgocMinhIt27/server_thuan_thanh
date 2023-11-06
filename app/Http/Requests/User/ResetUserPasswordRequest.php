<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\Regex;
use App\Models\Auth\Role;
use Hash;

class ResetUserPasswordRequest extends FormRequest
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
        $password = Regex::PASSWORD_REGEX;
        $rule = [
            'current_password' => 'required',
            'password' => "required|confirmed|regex:$password",
        ];
        return $rule;
    }
    public function messages()
    {
        return [
            // 'role_ids.required' => 'Vai trò người dùng không được để trống',
            'password.regex' => Regex::PASSWORD_MESSAGE,
        ];
    }
    public function withValidator($validator)
    {
        // checks user current password
        // before making changes
        $validator->after(function ($validator) {
            if (!Hash::check($this->current_password, $this->user()->password)) {
                $validator->errors()->add('current_password', 'Mật khẩu hiện giờ không đúng');
            }
        });
        return;
    }

    public function attributes()
    {
        return [];
    }
}
