<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum JournalEntryTypeEnum: string
{
    use EnumHelper;

    case TRANSACTION = 'transaction';
    case CURRENT_MONTH_EARNINGS = 'current_month_earnings';
    case MONTH_END_CLOSING = 'month_end_closing';
    case MONTH_TO_YEAR_CLOSING = 'month_to_year_closing';
    case YEAR_TO_RETAINED_EARNINGS_CLOSING = 'year_to_retained_earnings_closing';
}
