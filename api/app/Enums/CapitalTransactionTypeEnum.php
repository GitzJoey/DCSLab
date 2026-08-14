<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum CapitalTransactionTypeEnum: string
{
    use EnumHelper;

    case IN = 'IN';
    case OUT = 'OUT';
}
