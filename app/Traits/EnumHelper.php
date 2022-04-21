<?php

namespace App\Traits;

trait EnumHelper {
    public static function isValidName(string $name) : bool
    {
        $isValid = false;
        foreach(self::cases() as $enums) {
            if ($enums->name === $name) $isValid = true;
        }

        return $isValid;
    }

    public static function isValidValue($value) : bool
    {
        $isValid = false;
        foreach(self::cases() as $enums) {
            if ($enums->value === $value) $isValid = true;
        }

        return $isValid;
    }

    public static function isValid($test)
    {
        if (self::isValidName($test) || self::isValidValue($test)) return true;
        else return false;
    }

    public static function to($name)
    {
        foreach(self::cases() as $enums) {
            if ($enums->name === $name) return $enums;
        }
    }

    public static function tryTo($name)
    {
        self::to($name);
    }
}