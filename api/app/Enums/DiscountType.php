<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum DiscountType: string
{
    use EnumHelper;

    case PER_UNIT_PERCENT_DISCOUNT = 'PER_UNIT_PERCENT_DISCOUNT';
    case PER_UNIT_NOMINAL_DISCOUNT = 'PER_UNIT_NOMINAL_DISCOUNT';
    case PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT = 'PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT';
    case PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT = 'PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT';
    case GLOBAL_PERCENT_DISCOUNT = 'GLOBAL_PERCENT_DISCOUNT';
    case GLOBAL_NOMINAL_DISCOUNT = 'GLOBAL_NOMINAL_DISCOUNT';
}
