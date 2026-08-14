<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ProductStockFilterEnum: string
{
    use EnumHelper;

    case HAS_STOCK = 'has_stock';
    case EMPTY = 'empty';
    case VALID = 'valid';
    case INVALID = 'invalid';
}
