<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ProductType: int
{
    use EnumHelper;

    case RAW_MATERIAL = 1;
    case WORK_IN_PROGRESS = 2;
    case FINISHED_GOODS = 3;
    case SERVICE = 4;
}
