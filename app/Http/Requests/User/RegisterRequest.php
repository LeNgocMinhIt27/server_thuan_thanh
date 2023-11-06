<?php

namespace App\Http\Requests\User;

use App\Constants\Regex;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $mobile = Regex::MOBILE_REGEX;
        $mobile_not_regex = Regex::MOBILE_NOT_REGEX;
        return [
            'name' => 'required|max:255',
            'username' => "required|min:5|unique:users,username",
            'email' => 'email|max:255|unique:users,username',
            'password' => "required|max:255|min:8|regex:$password",
            'mobile' => "nullable|regex:$mobile|not_regex:$mobile_not_regex|min:10|max:11|unique:users,mobile",
            'confirm_password' => 'required|max:255|required_with:confirm_password|same:confirm_password|min:8',
        ];
    }
}
