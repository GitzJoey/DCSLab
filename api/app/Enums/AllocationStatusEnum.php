<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum AllocationStatusEnum: string
{
    use EnumHelper;

    case NOT_FULLY_ALLOCATED = 'not_fully_allocated';
    case FULLY_ALLOCATED = 'fully_allocated';
}
