<?php

namespace App\Http\Requests\User;

use App\Models\Auth\User;
use Illuminate\Foundation\Http\FormRequest;
use App\Constants\Regex;
use App\Models\Auth\Role;

class UserUpdateRequest extends FormRequest
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
        $user = User::where("id", $this->id)->first();

        $rule = [
            'username' => "required|min:4|unique:users,username,{$user->getKey()}",
            'roles' => "required"
        ];
        return $rule;
    }
    public function messages()
    {
        return [
            'password.regex' => Regex::PASSWORD_MESSAGE,
        ];
    }
    public function attributes()
    {
        return [
            'name' => 'tên hiển thị',
        ];
    }
}
