<?php

namespace App\DTOs;

final class JournalEntryUpdateDTO
{
    public function __construct(
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly ?string $journalType,
        public readonly ?string $referenceNo,
        public readonly ?string $remarks,
        /** @var JournalEntryItemDTO[] */
        public readonly array $items,
    ) {
    }
}
