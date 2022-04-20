<?php

namespace App\Enums;

enum ProductType: int
{
    case RAW_MATERIAL = 1;
    case WORK_IN_PROGRESS = 2;
    case FINISHED_GOODS = 3;
    case SERVICE = 4;

    public static function isValidName(string $name) : bool
    {
        switch ($name) 
        {
            case 'RAW_MATERIAL':
            case 'WORK_IN_PROGRESS':
            case 'FINISHED_GOODS':
            case 'SERVICE':
                return true;
            default:
                return false;
        }
    }
}