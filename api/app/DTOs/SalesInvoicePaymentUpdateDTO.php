<?php

namespace App\DTOs;

final class SalesInvoicePaymentUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly string $paymentType,
        public readonly ?int $cashAccountId,
        public readonly ?int $salesOrderPaymentId,
        public readonly ?int $salesReturnId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
