<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ExpenseCategoryTypeEnum: string
{
    use EnumHelper;

    case EXPENSE = 'expense';
    case OTHER_EXPENSE = 'other_expense';
}
