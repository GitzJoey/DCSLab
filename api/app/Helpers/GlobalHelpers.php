<?php

if (! function_exists('dec_trim')) {
    function dec_trim($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        return rtrim(rtrim($value, '0'), '.');
    }
}
