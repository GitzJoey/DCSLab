<?php

namespace App\Traits;

use Exception;

trait EnumHelper
{
    public static function isValidName(string $name): bool
    {
        $isValid = false;
        foreach (self::cases() as $enum) {
            if ($enum->name === $name) {
                $isValid = true;
            }
        }

        return $isValid;
    }

    public static function isValidValue($value): bool
    {
        $isValid = false;

        try {
            foreach (self::cases() as $enum) {
                if (gettype($enum->value) == 'integer' && $enum->value === (int) $value) {
                    $isValid = true;
                }
                if (gettype($enum->value) == 'string' && $enum->value === strval($value)) {
                    $isValid = true;
                }
            }
        } catch (Exception $e) {
            $isValid = false;
        }

        return $isValid;
    }

    public static function isValid($test): bool
    {
        if (is_null($test) || empty($test)) {
            return false;
        }

        if (self::isValidName($test) || self::isValidValue($test)) {
            return true;
        } else {
            return false;
        }
    }

    public static function fromName($name)
    {
        foreach (self::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum;
            }
        }
    }

    public static function tryFromName($name)
    {
        self::fromName($name);
    }

    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $enum) {
            $result[$enum->name] = $enum->value;
        }

        return $result;
    }

    public static function toArrayName(): array
    {
        $result = [];
        foreach (self::cases() as $enum) {
            array_push($result, $enum->name);
        }

        return $result;
    }

    public static function toArrayValue(): array
    {
        $result = [];
        foreach (self::cases() as $enum) {
            array_push($result, $enum->value);
        }

        return $result;
    }

    public static function toArrayEnum(): array
    {
        $result = [];
        foreach (self::cases() as $enum) {
            array_push($result, $enum);
        }

        return $result;
    }

    public static function resolveToEnum($val)
    {
        if (self::isValidName(strval($val))) {
            return self::fromName(strval($val));
        }

        if (self::isValidValue($val)) {
            return self::from($val);
        }

        return null;
    }
}
