<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum UserRole: string
{
    use EnumHelper;

    case USER = 'user';
    case DEVELOPER = 'developer';
    case ADMINISTRATOR = 'administrator';

    // region Extensions
    case POS_OWNER = 'POS-owner';
    // endregion
}
