<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ChartOfAccountNormalBalanceEnum: string
{
    use EnumHelper;

    case DEBIT = 'debit';
    case CREDIT = 'credit';
}
