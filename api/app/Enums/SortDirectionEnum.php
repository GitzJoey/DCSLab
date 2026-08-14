<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum SortDirectionEnum: string
{
    use EnumHelper;

    case ASC = 'asc';
    case DESC = 'desc';
}
