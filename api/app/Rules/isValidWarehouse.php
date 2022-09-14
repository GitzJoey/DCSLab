<?php

namespace App\Rules;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Rule;

class isValidWarehouse implements Rule
{
    private $companyId;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($company_id)
    {
        $this->companyId = $company_id;
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
        if (!$this->companyId) return false;
        
        if ($value) {
            $result = Warehouse::where([
                ['id', '=', $value],
                ['company_id', '=', $this->companyId],
            ])->exists();
            return $result;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_warehouse');
    }
}
