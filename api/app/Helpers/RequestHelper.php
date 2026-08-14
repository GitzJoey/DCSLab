<?php

namespace App\Helpers;

class RequestHelper
{
    public static function safeReturn(?object $input)
    {
        try {
            if ($input) {
                return $input;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
