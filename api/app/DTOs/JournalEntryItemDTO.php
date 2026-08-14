<?php

namespace App\DTOs;

final class JournalEntryItemDTO
{
    public function __construct(
        public readonly ?int $chartOfAccountId,
        public readonly int $sequence,
        public readonly float $debit,
        public readonly float $credit,
        public readonly ?string $remarks,
    ) {
    }
}
