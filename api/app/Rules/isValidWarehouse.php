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
    public function __construct($companyId, $branchId)
    {
        $this->companyId = $companyId;
        $this->branchId = $branchId;
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
        if (!$this->companyId || !$this->branchId || !$value) return false;

        $result = Warehouse::where([
            ['id', '=', $value],
            ['company_id', '=', $this->companyId],
            ['branch_id', '=', $this->branchId],
        ])->exists();

        return $result;
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
