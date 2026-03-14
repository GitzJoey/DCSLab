<?php

namespace App\DTOs;

final class ProductImageDTO
{
    public function __construct(
        public readonly string $hash,
        public readonly bool $isThumbnail,
    ) {
    }
}
