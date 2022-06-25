<?php

namespace App\Traits;

trait EnumHelper {
    public static function isValidName(string $name): bool
    {
        $isValid = false;
        foreach(self::cases() as $enum) {
            if ($enum->name === $name) $isValid = true;
        }

        return $isValid;
    }

    public static function isValidValue($value): bool
    {
        $isValid = false;
        foreach(self::cases() as $enum) {
            if ($enum->value === $value) $isValid = true;
        }

        return $isValid;
    }

    public static function isValid($test): bool
    {
        if (self::isValidName($test) || self::isValidValue($test)) return true;
        else return false;
    }

    public static function fromName($name)
    {
        foreach(self::cases() as $enum) {
            if ($enum->name === $name) return $enum;
        }
    }

    public static function tryFromName($name)
    {
        self::fromName($name);
    }

    public static function toArray(): array
    {
        $result = [];
        foreach(self::cases() as $enum) {
            $result[$enum->name] = $enum->value;
        }
        return $result;
    }

    public static function toArrayName(): array
    {
        $result = [];
        foreach(self::cases() as $enum) {
            array_push($result, $enum->name);
        }
        return $result;
    }

    public static function toArrayValue(): array
    {
        $result = [];
        foreach(self::cases() as $enum) {
            array_push($result, $enum->value);
        }
        return $result;
    }
}