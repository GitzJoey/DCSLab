<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ProductGroupCategory: string
{
    use EnumHelper;
    
    case PRODUCTS = 'PRD';
    case SERVICES = 'SVC';
    case PRODUCTS_AND_SERVICES = 'PRDSVC';
}