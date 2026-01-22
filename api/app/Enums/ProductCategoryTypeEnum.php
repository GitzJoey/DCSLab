<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ProductCategoryTypeEnum: int
{
    use EnumHelper;

    case PRODUCT = 1;
    case SERVICE = 2;
}
