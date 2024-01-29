<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum VatStatus: int
{
    use EnumHelper;

    case NON_VAT = 0;
    case INCLUDE_VAT = 1;
    case EXCLUDE_VAT = 2;
}
