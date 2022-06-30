<?php

namespace App\Actions;

use Illuminate\Support\Facades\Config;

class RandomGenerator
{
    private static $RSeed = 0;

    public function seed(int $s = 0): int 
    {
        self::$RSeed = abs(intval($s)) % 9999999 + 1;
        return $this->generateNumber();
    }

    public function generateNumber(int $min = 0, int $max = 9999999): int
    {
        if ($max == 0) return 0;

        if (self::$RSeed == 0)
            $this->seed(random_int(0, mt_getrandmax()));

        self::$RSeed = (self::$RSeed * 125) % 2796203;

        return self::$RSeed % ($max - $min + 1) + $min;
    }

    public function generateRandomTimer(): string
    {
        return random_int(0,23).":".str_pad(random_int(0,59), 2, "0", STR_PAD_LEFT).":".str_pad(random_int(0,59), 2, "0", STR_PAD_LEFT);
    }

    public function generateFixedLengthNumber(int $length = 2): int
    {
        if ($length < 2) $length = 2;

        return random_int(intVal(pow(10, $length - 1)), intVal(pow(10, $length) - 1));
    }

    public function generateOne(int $max = 0): int
    {
        if ($max == 0) {
            return $this->generateNumber(0, 9999999);
        } else {
            return $this->generateNumber(0, intVal($max));
        }
    }

    public function generateAlphaNumeric(int $length): string
    {
        $generatedString = '';
        $characters = array_merge(
            ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
            [3, 4, 7, 9]);
        $max = sizeof($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $generatedString .= $characters[random_int(0, $max)];
        }

        return strtoupper($generatedString);
    }

    public function randomTrueOrFalse(int $howManyTimes = 1): bool | array
    {
        if ($howManyTimes <= 1) return (bool)random_int(0,1);

        $result = array();
        
        for($i = 0; $i < $howManyTimes; $i++) {
            $result[$i] = (bool)random_int(0,1);
        }

        return $result;
    }

    public function generateRandomOneZero(int $maxZero = 1): int
    {
        if ($maxZero == 1) return 10;

        $rand = random_int(2, $maxZero);

        return pow(10, $rand);
    }
}
