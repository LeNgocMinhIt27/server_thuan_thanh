<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'section_name',
        'setting_name',
        'setting_value',
        'setting_type',
        'ki_hoc'
    ];
    protected $casts = [
        'setting_value' => \App\Casts\TypeValueCast::class . ':setting_type'
    ];
    public function scopeGetValueByKey($query, $key)
    {
        if (empty($key)) {
            return null;
        }
        $keys = explode(".", $key);
        if (count($keys) == 2) {
            $data = $query->where('section_name', $keys[0])->where('setting_name', $keys[1])->first();
        } else if (count($keys) == 1) {
            $data = $query->where('section_name', $keys[0])->first();
        }
        if (isset($data)) {
            return $data->setting_value;
        }
        return null;
    }
}
