<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ProductCategory: int
{
    use EnumHelper;

    case PRODUCTS = 1;
    case SERVICES = 2;
    case PRODUCTS_AND_SERVICES = 3;
}
