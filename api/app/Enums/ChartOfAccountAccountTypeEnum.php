<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ChartOfAccountAccountTypeEnum: string
{
    use EnumHelper;

    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case SYSTEM = 'system';
}
