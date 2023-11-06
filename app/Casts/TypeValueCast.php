<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Str;

class TypeValueCast implements CastsAttributes
{
    protected $field_type;
    public function __construct($field_type = null)
    {
        $this->field_type = $field_type;
    }
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     */
    public function get($model, string $key, $value, $attributes)
    {
        if (Str::contains($model[$this->field_type], 'App\Models')) {
            return $model[$this->field_type]::find($value);
        }
        switch ($model[$this->field_type]) {
            case 'number':
                return (float) $value;
            case 'boolean':
                return (bool) $value;
            case 'json':
                return json_decode($value);
            default:
                return $value;
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        switch ($model[$this->field_type]) {
            case 'number':
                return (float) $value;
            case 'boolean':
                return (bool) $value;
            case 'json':
                return json_encode($value);
            default:
                return $value;
        }
    }
}
