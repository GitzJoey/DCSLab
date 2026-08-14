<?php

namespace App\DTOs;

final class PurchaseInvoicePaymentUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly string $paymentType,
        public readonly ?int $cashAccountId,
        public readonly ?int $purchaseOrderPaymentId,
        public readonly ?int $purchaseReturnId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
