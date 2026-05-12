<?php

namespace App\DTOs;

final class JournalEntryLineDTO
{
    public function __construct(
        public readonly int $chartOfAccountId,
        public readonly float $debit,
        public readonly float $credit,
        public readonly ?string $remarks,
    ) {
    }
}
