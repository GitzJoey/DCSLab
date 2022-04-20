<?php

namespace App\Enums;

enum UnitCategory: int
{
    case PRODUCTS = 1;
    case SERVICES = 2;
    case PRODUCTS_AND_SERVICES = 3;

    public static function isValidName(string $name) : bool
    {
        switch ($name) 
        {
            case 'PRODUCTS';
            case 'SERVICES';
            case 'PRODUCTS_AND_SERVICES';
                return true;
            default:
                return false;
        }
    }
}