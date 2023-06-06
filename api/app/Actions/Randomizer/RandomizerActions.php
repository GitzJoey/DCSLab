<?php

namespace App\Actions\Randomizer;

class RandomizerActions
{
    private array $alphabet_characters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    private array $numeric_characters = [3, 4, 7, 9];

    public function __construct()
    {
    }

    public function generateRandomTimer(): string
    {
        return random_int(0, 23) . ':' . str_pad(random_int(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(random_int(0, 59), 2, '0', STR_PAD_LEFT);
    }

    public function generateNumeric(int $length = 3): int
    {
        if ($length < 2) {
            $length = 2;
        }

        return random_int(intval(pow(10, $length - 1)), intval(pow(10, $length) - 1));
    }

    public function generateAlphaNumeric(int $length = 7): string
    {
        $generatedString = '';
        $characters = array_merge($this->alphabet_characters, $this->numeric_characters);
        $max = count($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $generatedString .= $characters[random_int(0, $max)];
        }

        return strtoupper($generatedString);
    }

    public function generateAlpha(int $length = 4): string
    {
        $generatedString = '';
        $max = count($this->alphabet_characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $generatedString .= $this->alphabet_characters[random_int(0, $max)];
        }

        return strtoupper($generatedString);
    }
}
