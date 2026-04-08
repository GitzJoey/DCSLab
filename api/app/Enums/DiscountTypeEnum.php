<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum DiscountTypeEnum: string
{
    use EnumHelper;

    case PERCENTAGE = 'PERCENTAGE';
    case NOMINAL = 'NOMINAL';
}
