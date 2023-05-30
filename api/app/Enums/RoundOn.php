<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum RoundOn: int
{
    use EnumHelper;

    case UP = 1;
    case DOWN = 2;
}
