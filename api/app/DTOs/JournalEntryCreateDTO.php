<?php

namespace App\DTOs;

final class JournalEntryCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly ?string $sourceType,
        public readonly ?int $sourceId,
        public readonly ?string $referenceNo,
        public readonly ?string $remarks,
        /** @var JournalEntryItemDTO[] */
        public readonly array $items,
    ) {
    }
}
