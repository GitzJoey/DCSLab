<?php

namespace App\Enums;

enum UserRoles: string
{
    case USER = 'user';
    case DEVELOPER = 'dev';
    case ADMINISTRATOR = 'administrator';

    case BUSINESS_OWNER = 'business_owner';
}