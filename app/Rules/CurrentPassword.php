<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CurrentPassword implements Rule
{
    private $currentpasswrod;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($currentpasswrod)
    {
        $this->currentpasswrod = $currentpasswrod;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return \Hash::check($value, $this->currentpasswrod);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.currentpassword');
    }
}
