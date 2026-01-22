<?php

namespace App\Rules;

use App\Actions\Employee\EmployeeActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmployeeUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $employee;

    public function __construct($companyId, $employee)
    {
        $this->companyId = $companyId;
        $this->employee = $employee;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $employeeActions = new EmployeeActions();

            if (! $employeeActions->isUniqueCode($this->companyId, $value, $this->employee->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
