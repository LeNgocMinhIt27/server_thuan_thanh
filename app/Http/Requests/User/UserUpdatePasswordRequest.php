<?php

namespace App\Http\Requests\User;

use App\Rules\CurrentPassword;
use Illuminate\Foundation\Http\FormRequest;
use App\Constants\Regex;

class UserUpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $currentuser = $this->user();
        $idUserUpdate = $this->route('id');
        if (isset($idUserUpdate)) {
            return $currentuser->isAdmin() || $idUserUpdate == $currentuser->getKey();
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentuser = $this->user();
        $idUserUpdate = $this->route('id');
        $password = Regex::PASSWORD_REGEX;
        if ($currentuser->isAdmin() && $idUserUpdate) {
            return [
                'confirm_password' => 'required|max:255|min:8',
                'password' => "required|max:255|min:8|required_with:confirm_password|same:confirm_password|regex:$password",
            ];
        } else {
            return [
                'oldpassword' => ['required', 'max:255', 'min:8', new CurrentPassword($currentuser->password)],
                'confirm_password' => 'required|max:255|min:8',
                'password' => "required|max:255|min:8|required_with:confirm_password|same:confirm_password|regex:$password",
            ];
        }
    }
    public function messages()
    {
        return [
            'password.regex' => Regex::PASSWORD_MESSAGE,
        ];
    }
}
