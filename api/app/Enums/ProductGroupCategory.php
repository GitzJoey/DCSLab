<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ProductGroupCategory: int
{
    use EnumHelper;

    case PRODUCTS = 1;
    case SERVICES = 2;
}
