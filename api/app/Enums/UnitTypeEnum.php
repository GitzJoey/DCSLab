<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum UnitTypeEnum: int
{
    use EnumHelper;

    case PRODUCT = 1;
    case SERVICE = 2;
}
