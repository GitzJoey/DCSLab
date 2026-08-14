<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ChartOfAccountScopeEnum: string
{
    use EnumHelper;

    case SYSTEM = 'system';
    case USER = 'user';
}
