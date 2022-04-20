<?php

namespace App\Enums;

enum PaymentTerm: string
{
    case PAYMENT_IN_ADVANCE = 'PIA';
    case X_DAYS_AFTER_INVOICE = 'NET';
    case END_OF_MONTH = 'EOM';
    case CASH_ON_DELIVERY = 'COD';
    case CASH_ON_NEXT_DELIVERY = 'CND';
    case CASH_BEFORE_SHIPMENT = 'CBS';

    public static function isValidName(string $name) : bool
    {
        switch ($name) 
        {
            case 'PAYMENT_IN_ADVANCE':
            case 'X_DAYS_AFTER_INVOICE':
            case 'END_OF_MONTH':
            case 'CASH_ON_DELIVERY':
            case 'CASH_ON_NEXT_DELIVERY':
            case 'CASH_BEFORE_SHIPMENT':
                return true;
            default:
                return false;
        }
    }
}