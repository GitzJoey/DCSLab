<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum PaymentTypeEnum: string
{
    use EnumHelper;

    case CASH = 'cash';
    case DOWN_PAYMENT = 'down_payment';
    case RETURN = 'return';
}
