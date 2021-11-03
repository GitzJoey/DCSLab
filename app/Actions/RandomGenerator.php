<?php

namespace App\Actions;

use Illuminate\Support\Facades\Config;

class RandomGenerator
{
    private static $RSeed = 0;

    public function seed($s = 0)
    {
        self::$RSeed = abs(intval($s)) % 9999999 + 1;
        self::generateNumber();
    }

    public function generateNumber($min = 0, $max = 9999999)
    {
        if (self::$RSeed == 0)
            self::seed(mt_rand());

        self::$RSeed = (self::$RSeed * 125) % 2796203;

        return self::$RSeed % ($max - $min + 1) + $min;
    }

    public function generateOne($max = 0)
    {
        if ($max == 0) {
            return self::generateNumber(0, 9999999);
        } else {
            return self::generateNumber(0, $max);
        }
    }

    public function generateAlphaNumeric($length)
    {
        $generatedString = '';
        $characters = array_merge(Config::get('const.DEFAULT.RANDOMSTRINGRANGE.ALPHABET'), Config::get('const.DEFAULT.RANDOMSTRINGRANGE.NUMERIC'));
        $max = sizeof($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $generatedString .= $characters[mt_rand(0, $max)];
        }

        return strtoupper($generatedString);

    }
}
