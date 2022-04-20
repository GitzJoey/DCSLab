<?php

namespace App\Enums;

enum ActiveStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public static function isValidName(string $name) : bool
    {
        switch ($name) 
        {
            case 'ACTIVE':
            case 'INACTIVE':
                return true;
            default:
                return false;
        }
    }
}