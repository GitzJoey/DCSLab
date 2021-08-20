<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Config;

class validDropDownValue implements Rule
{
    private $dropDownType;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dropDownType)
    {
        $this->dropDownType = $dropDownType;
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
        $result = false;

        $typeList = Config::get('const.DROPDOWN');
        
        foreach ($typeList as $key => $val) {
            if ($key == $this->dropDownType) {
                foreach ($val as $k => $v) {
                    if ($v == $value) {
                        $result = true;
                        return $result;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_dropdown');
    }
}
