<?php

namespace App\DTOs;

use App\Enums\CapitalTransactionTypeEnum;
use App\Models\CapitalOpening;
use App\Models\CapitalTransaction;
use App\Models\PurchaseOrderDownPayment;
use App\Models\PurchaseOrderDownPaymentRefund;

final class CashTransactionCreateDTO
{
    public function __construct(
        public readonly string $referableType,
        public readonly int $referableId,
        public readonly string $date,
        public readonly int $cashAccountId,
        public readonly float $amount,
    ) {
    }

    public static function fromCapitalOpening(CapitalOpening $capitalOpening): self
    {
        return new self(
            referableType: CapitalOpening::class,
            referableId: $capitalOpening->id,
            date: $capitalOpening->date,
            cashAccountId: $capitalOpening->cash_account_id,
            amount: (float) $capitalOpening->amount,
        );
    }

    public static function fromCapitalTransaction(CapitalTransaction $capitalTransaction): self
    {
        $type = $capitalTransaction->type instanceof CapitalTransactionTypeEnum ? $capitalTransaction->type : CapitalTransactionTypeEnum::resolveToEnum($capitalTransaction->type);
        $amount = $type === CapitalTransactionTypeEnum::IN ? (float) $capitalTransaction->amount : ((float) $capitalTransaction->amount * -1);

        return new self(
            referableType: CapitalTransaction::class,
            referableId: $capitalTransaction->id,
            date: $capitalTransaction->date,
            cashAccountId: $capitalTransaction->cash_account_id,
            amount: $amount,
        );
    }

    public static function fromPurchaseOrderDownPayment(PurchaseOrderDownPayment $purchaseOrderDownPayment): self
    {
        return new self(
            referableType: PurchaseOrderDownPayment::class,
            referableId: $purchaseOrderDownPayment->id,
            date: $purchaseOrderDownPayment->date,
            cashAccountId: $purchaseOrderDownPayment->cash_account_id,
            amount: (float) $purchaseOrderDownPayment->amount,
        );
    }

    public static function fromPurchaseOrderDownPaymentRefund(PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund): self
    {
        return new self(
            referableType: PurchaseOrderDownPaymentRefund::class,
            referableId: $purchaseOrderDownPaymentRefund->id,
            date: $purchaseOrderDownPaymentRefund->date,
            cashAccountId: $purchaseOrderDownPaymentRefund->cash_account_id,
            amount: (float) $purchaseOrderDownPaymentRefund->amount,
        );
    }
}
