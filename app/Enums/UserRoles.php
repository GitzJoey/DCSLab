<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum UserRoles: string
{
    use EnumHelper;

    case USER = 'user';
    case DEVELOPER = 'dev';
    case ADMINISTRATOR = 'administrator';

    case BUSINESS_OWNER = 'business_owner';
}