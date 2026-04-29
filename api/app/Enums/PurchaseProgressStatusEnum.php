<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum PurchaseProgressStatusEnum: string
{
    use EnumHelper;

    case UNLINKED = 'unlinked';
    case UNMATCHED = 'unmatched';
    case MATCHED = 'matched';

    public static function toDropDownOptions(string $translationPrefix): array
    {
        return array_map(
            fn (self $enum) => [
                'name' => $translationPrefix.$enum->value,
                'code' => $enum->value,
            ],
            self::cases(),
        );
    }
}
