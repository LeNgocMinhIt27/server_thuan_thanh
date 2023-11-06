<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\Regex;
use App\Models\Auth\Role;

class UserRequest extends FormRequest
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
            'username' => [
                "required", "string", "min:5",
                "unique:users,username",
            ],
            'password' => "required|max:255|min:8|regex:$password",
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
    public function attributes()
    {
        return [
            'username' => 'tên hiển thị',
        ];
    }
}
