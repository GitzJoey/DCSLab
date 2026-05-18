<?php

namespace App\Traits;

trait EnumHelper
{
    /**
     * Get all case values as a simple array.
     * Useful for: Validation rules ['role' => ['in:' . implode(',', UserRole::values())]]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all case names as a simple array.
     * Useful for: Debugging or internal mapping.
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get an associative array of [value => Name].
     * Useful for: Select dropdowns in Blade/Livewire.
     */
    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    /**
     * Get an associative array of [value => Label].
     * Requires the Enum to have a 'label()' method.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => method_exists($case, 'label') ? $case->label() : $case->name,
        ])->toArray();
    }

    /**
     * Check if a specific value exists in the Enum cases.
     */
    public static function isValid(mixed $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
