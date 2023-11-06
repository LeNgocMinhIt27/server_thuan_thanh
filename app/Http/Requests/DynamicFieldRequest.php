<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DynamicFieldRequest extends FormRequest
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
        $id = $this->route('truong');
        return [
            'ten' => [
                'required',
                'max:255',
                'min:1',
                Rule::unique('db_fields', 'ten')->where('db_table_id', $this->input('db_table_id'))->ignore($id),
            ],
            'ma' => [
                'required',
                'max:255',
                'min:1',
                Rule::unique('db_fields', 'ma')->where('db_table_id', $this->input('db_table_id'))->ignore($id),
            ],
            'loai' => 'required',
        ];
    }
}
