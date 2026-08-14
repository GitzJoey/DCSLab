<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class IsValidDate implements ValidationRule
{
    public function __construct(
        private string $format
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $autoKeyword = Config::get('dcslab.KEYWORDS.AUTO');

        if ($value === $autoKeyword) {
            return;
        }

        if (! is_string($value)) {
            $fail(trans('validation.date_format', ['attribute' => ':attribute', 'format' => $this->format]));

            return;
        }

        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => 'date_format:'.$this->format]
        );

        if ($validator->fails()) {
            $fail(trans('validation.date_format', ['attribute' => ':attribute', 'format' => $this->format]));
        }
    }
}
