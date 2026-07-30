<?php

namespace App\DTOs;

final class PurchaseOrderReceiptUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $supplierId,
        public readonly int $purchaseOrderId,
        public readonly int $warehouseId,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        /** @var array<int, int> */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> each item: id, purchase_order_item_id, qty, product_unit_id, product_unit_conversion_value, remarks, delete_serial_ids[], serials[] */
        public readonly array $items,
        /** @var array<int, int> */
        public readonly array $deleteCostIds,
        /** @var array<int, array<string, mixed>> each cost: id, code, date, name, cash_account_id, amount, remarks */
        public readonly array $costs,
    ) {
    }
}
