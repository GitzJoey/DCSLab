<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum PaymentTermType: string
{
    use EnumHelper;
    
    case PAYMENT_IN_ADVANCE = 'PIA';
    case X_DAYS_AFTER_INVOICE = 'NET';
    case END_OF_MONTH = 'EOM';
    case CASH_ON_DELIVERY = 'COD';
    case CASH_ON_NEXT_DELIVERY = 'CND';
    case CASH_BEFORE_SHIPMENT = 'CBS';
}