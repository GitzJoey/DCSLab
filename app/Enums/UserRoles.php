<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum UserRoles: string
{
    use EnumHelper;

    case USER = 'user';
    case DEVELOPER = 'dev';
    case ADMINISTRATOR = 'administrator';

    #region Extensions
    case POS_OWNER = 'POS-owner';
    case POS_EMPLOYEE = 'POS-employee';
    #endregion
}