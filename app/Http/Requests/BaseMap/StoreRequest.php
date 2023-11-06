<?php

namespace App\Http\Requests\BaseMap;

use App\Constants\Regex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Str;

class StoreRequest extends FormRequest
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
    protected function prepareForValidation()
    {
        $meta = $this->meta;
        if (is_string($meta)) {
            $meta = json_decode($meta, true);
        }
        $this->merge([
            'meta' => $meta,
            'minzoom' => $meta['minzoom'] ?? 0,
            'maxzoom' => $meta['maxzoom'] ?? 24
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $url_regex = Regex::URL_REGEX;
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|max:191',
            'link' => ["required", "max:191", "regex:$url_regex"],
        ];
        if (Str::startsWith($this->link, ['/styles/', '/map/', '/data/', '/api-trinh-bay/'])) {
            $rules['link'] = ["required", "max:191"];
        }
        if (!empty($this->id) && !empty($this->thumbnail) && !is_string($this->thumbnail)) {
            $rules['thumbnail'] = 'required|mimes:jpeg,jpg,png|max:1024'; //max 1mb
        }
        if ($this->type == 'raster') {
            $rules['maxzoom'] = [
                'nullable', 'gte:minzoom', 'integer', 'min:0', 'max:24'
            ];
            $rules['minzoom'] = [
                'nullable', 'integer', 'min:0', 'max:24'
            ];
        }
        return $rules;
    }
    public function messages()
    {
        return [
            'link.required'  => "Đường dẫn không được để trống",
            'link.url'  => "Đường dẫn không đúng định dạng",
            'maxzoom.gte'  => "Mức thu phóng tối đa phải lớn hơn mức thu phóng tối thiếu",
            'thumbnail.required'  => "Hình ảnh đính kèm không được để trống",
        ];
    }
    public function attributes()
    {
        return [
            'maxzoom' => 'mức thu phóng tối đa',
            'minzoom' => 'mức thu phóng tối thiếu',
            'thumbnail' => 'hhình ảnh đính kèm',
            'link' => 'đường dẫn',
        ];
    }
}
