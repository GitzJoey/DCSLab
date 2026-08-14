<?php

namespace App\Rules;

use App\Enums\RecordStatusEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchStoreValidStatus implements ValidationRule
{
    private ?bool $isMain;

    public function __construct(?bool $isMain)
    {
        $this->isMain = $isMain;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        return;
        if ($this->isMain == true && $value == RecordStatusEnum::INACTIVE->value) {
            $fail('rules.branch.set_branch_to_non_main')->translate();

            return;
        }
    }
}
