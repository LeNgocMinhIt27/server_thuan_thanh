<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiemDanhRequest extends FormRequest
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
            'lan_diem_danh_id' => 'required|integer',
            'sinh_vien_id' => 'required|integer',
        ];
    }
    public function messages()
    {
        return [
            'lan_diem_danh_id.required' => 'Phải có thông tin lần điểm danh ',
            'sinh_vien_id.required' => 'ID sinh viên là bắt buộc',
            'lan_diem_danh_id.integer' => 'Thông tin lần điểm danh là giá trị số nguyên',
            'sinh_vien_id.integer' => 'ID sinh viên là bắt buộc có giá trị là số nguyên',
        ];
    }
}
