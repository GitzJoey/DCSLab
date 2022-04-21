<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ActiveStatus: int
{
    use EnumHelper;

    case ACTIVE = 1;
    case INACTIVE = 0;
}