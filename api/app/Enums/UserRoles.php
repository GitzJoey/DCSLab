<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum UserRoles: string
{
    use EnumHelper;

    case USER = 'user';
    case DEVELOPER = 'developer';
    case ADMINISTRATOR = 'administrator';

    //region Extensions
    case POS_OWNER = 'POS-owner';
    case POS_EMPLOYEE = 'POS-employee';
    case POS_SUPPLIER = 'POS-supplier';
    case POS_CUSTOMER = 'POS-customer';
    //endregion
}
