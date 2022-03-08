<?php

namespace App\Enums;

enum UnitCategory: int
{
    case PRODUCTS = 1;
    case SERVICES = 2;
    case PRODUCTS_AND_SERVICES = 3;
}