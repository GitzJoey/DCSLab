<?php

namespace App\Actions\PurchaseOrderReceipt;

use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Actions\PurchaseOrderReceiptCost\PurchaseOrderReceiptCostActions;
use App\Actions\PurchaseOrderReceiptItem\PurchaseOrderReceiptItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseOrderReceiptCostCreateDTO;
use App\DTOs\PurchaseOrderReceiptCostUpdateDTO;
use App\DTOs\PurchaseOrderReceiptCreateDTO;
use App\DTOs\PurchaseOrderReceiptItemCreateDTO;
use App\DTOs\PurchaseOrderReceiptItemUpdateDTO;
use App\DTOs\PurchaseOrderReceiptUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseReturnItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderReceiptActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly PurchaseOrderReceiptItemActions $purchaseOrderReceiptItemActions,
        private readonly PurchaseOrderReceiptCostActions $purchaseOrderReceiptCostActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseOrder.supplier',
        'warehouse',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseOrder.supplier',
        'warehouse',
        'items.purchaseOrderItem',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.serials',
        'costs.cashAccount',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $supplierId,
        ?int $purchaseOrderId,
        ?string $startDate,
        ?string $endDate,
        ?int $warehouseId,
        ?bool $isPosted,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderReceipt::select('purchase_order_receipts.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'purchase_order_receipts.company_id')
            ->whereCompanyId('purchase_order_receipts', $companyId)
            ->whereBranchId('purchase_order_receipts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $supplierId,
            $purchaseOrderId,
            $startDate,
            $endDate,
            $warehouseId,
            $isPosted,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_order_receipts.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_order_receipts.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($supplierId) {
                $query->where('purchase_order_receipts.supplier_id', $supplierId);
            }

            if ($purchaseOrderId) {
                $query->where('purchase_order_receipts.purchase_order_id', $purchaseOrderId);
            }

            if ($startDate) {
                $query->where('purchase_order_receipts.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_order_receipts.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($warehouseId) {
                $query->where('purchase_order_receipts.warehouse_id', $warehouseId);
            }

            if (! is_null($isPosted)) {
                $query->where('purchase_order_receipts.is_posted', $isPosted);
            }
        });

        $query->orderBy('purchase_order_receipts.date', 'desc')
            ->orderBy('purchase_order_receipts.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $supplierId ?? '[null]',
                    $purchaseOrderId ?? '[null]',
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $warehouseId ?? '[null]',
                    is_null($isPosted) ? '[null]' : ($isPosted ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_receipt_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
                    $this->saveToCache($cacheKey, $result);
                }

                return $result;
            } catch (Exception $e) {
                $this->loggerDebug(__METHOD__, $e);
                throw $e;
            } finally {
                $execution_time = microtime(true) - $timer_start;
                $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
            }
        }

        return $query;
    }

    public function read(PurchaseOrderReceipt $purchaseOrderReceipt): PurchaseOrderReceipt
    {
        return $purchaseOrderReceipt->load(self::DETAIL_EAGER_LOADS);
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == Config::get('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = PurchaseOrderReceipt::whereCompanyId('purchase_order_receipts', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PRC'.str_pad($count, 5, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderReceipt::whereCompanyId('purchase_order_receipts', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseOrderReceiptCreateDTO $data): PurchaseOrderReceipt
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceipt = new PurchaseOrderReceipt();
            $purchaseOrderReceipt->company_id = $data->companyId;
            $purchaseOrderReceipt->branch_id = $data->branchId;
            $purchaseOrderReceipt->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrderReceipt->date = $this->generateDate($data->date);
            $purchaseOrderReceipt->supplier_id = $data->supplierId;
            $purchaseOrderReceipt->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderReceipt->warehouse_id = $data->warehouseId;
            $purchaseOrderReceipt->remarks = $data->remarks;
            $purchaseOrderReceipt->is_posted = $data->isPosted;
            $purchaseOrderReceipt->save();

            foreach ($data->items as $item) {
                $dto = new PurchaseOrderReceiptItemCreateDTO(
                    companyId: $purchaseOrderReceipt->company_id,
                    branchId: $purchaseOrderReceipt->branch_id,
                    purchaseOrderReceiptId: $purchaseOrderReceipt->id,
                    purchaseOrderItemId: $item['purchase_order_item_id'],
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );

                $this->purchaseOrderReceiptItemActions->create($dto);
            }

            foreach ($data->costs as $cost) {
                $dto = new PurchaseOrderReceiptCostCreateDTO(
                    companyId: $purchaseOrderReceipt->company_id,
                    branchId: $purchaseOrderReceipt->branch_id,
                    purchaseOrderReceiptId: $purchaseOrderReceipt->id,
                    code: $cost['code'],
                    date: $cost['date'],
                    name: $cost['name'],
                    cashAccountId: $cost['cash_account_id'],
                    amount: $cost['amount'],
                    remarks: $cost['remarks'],
                );

                $this->purchaseOrderReceiptCostActions->create($dto);
            }

            self::updateSummary($purchaseOrderReceipt);

            $this->syncJournalEntry($purchaseOrderReceipt);

            PurchaseOrderActions::updateSummary($purchaseOrderReceipt->purchaseOrder);

            $this->flushCache();

            return $purchaseOrderReceipt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderReceipt $purchaseOrderReceipt, PurchaseOrderReceiptUpdateDTO $data): PurchaseOrderReceipt
    {
        $timer_start = microtime(true);

        try {
            $previousPurchaseOrder = $purchaseOrderReceipt->purchaseOrder;

            $purchaseOrderReceipt->code = $this->generateUniqueCode($purchaseOrderReceipt->company_id, $data->code, $purchaseOrderReceipt->id);
            $purchaseOrderReceipt->date = $this->generateDate($data->date);
            $purchaseOrderReceipt->supplier_id = $data->supplierId;
            $purchaseOrderReceipt->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderReceipt->warehouse_id = $data->warehouseId;
            $purchaseOrderReceipt->remarks = $data->remarks;
            $purchaseOrderReceipt->is_posted = $data->isPosted;
            $purchaseOrderReceipt->save();
            $purchaseOrderReceipt->unsetRelation('purchaseOrder');

            foreach ($data->deleteItemIds as $deleteId) {
                $purchaseOrderReceiptItem = $purchaseOrderReceipt->items()->findOrFail($deleteId);
                $this->purchaseOrderReceiptItemActions->delete($purchaseOrderReceiptItem);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $purchaseOrderReceiptItem = $purchaseOrderReceipt->items()->findOrFail($item['id']);
                    $dto = new PurchaseOrderReceiptItemUpdateDTO(
                        purchaseOrderItemId: $item['purchase_order_item_id'],
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        remarks: $item['remarks'],
                        deleteSerialIds: $item['delete_serial_ids'],
                        serials: $item['serials'],
                    );

                    $this->purchaseOrderReceiptItemActions->update($purchaseOrderReceiptItem, $dto);
                } else {
                    $dto = new PurchaseOrderReceiptItemCreateDTO(
                        companyId: $purchaseOrderReceipt->company_id,
                        branchId: $purchaseOrderReceipt->branch_id,
                        purchaseOrderReceiptId: $purchaseOrderReceipt->id,
                        purchaseOrderItemId: $item['purchase_order_item_id'],
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        remarks: $item['remarks'],
                        serials: $item['serials'],
                    );

                    $this->purchaseOrderReceiptItemActions->create($dto);
                }
            }

            foreach ($data->deleteCostIds as $deleteId) {
                $purchaseOrderReceiptCost = $purchaseOrderReceipt->costs()->findOrFail($deleteId);
                $this->purchaseOrderReceiptCostActions->delete($purchaseOrderReceiptCost);
            }

            foreach ($data->costs as $cost) {
                if (! empty($cost['id'])) {
                    $purchaseOrderReceiptCost = $purchaseOrderReceipt->costs()->findOrFail($cost['id']);
                    $dto = new PurchaseOrderReceiptCostUpdateDTO(
                        code: $cost['code'],
                        date: $cost['date'],
                        name: $cost['name'],
                        cashAccountId: $cost['cash_account_id'],
                        amount: $cost['amount'],
                        remarks: $cost['remarks'],
                    );

                    $this->purchaseOrderReceiptCostActions->update($purchaseOrderReceiptCost, $dto);
                } else {
                    $dto = new PurchaseOrderReceiptCostCreateDTO(
                        companyId: $purchaseOrderReceipt->company_id,
                        branchId: $purchaseOrderReceipt->branch_id,
                        purchaseOrderReceiptId: $purchaseOrderReceipt->id,
                        code: $cost['code'],
                        date: $cost['date'],
                        name: $cost['name'],
                        cashAccountId: $cost['cash_account_id'],
                        amount: $cost['amount'],
                        remarks: $cost['remarks'],
                    );

                    $this->purchaseOrderReceiptCostActions->create($dto);
                }
            }

            self::updateSummary($purchaseOrderReceipt);

            $this->syncJournalEntry($purchaseOrderReceipt);

            if ($previousPurchaseOrder) {
                PurchaseOrderActions::updateSummary($previousPurchaseOrder->refresh());
            }

            if ($purchaseOrderReceipt->purchaseOrder) {
                if (! $previousPurchaseOrder || $purchaseOrderReceipt->purchaseOrder->id !== $previousPurchaseOrder->id) {
                    PurchaseOrderActions::updateSummary($purchaseOrderReceipt->purchaseOrder);
                } else {
                    PurchaseOrderActions::updateSummary($purchaseOrderReceipt->purchaseOrder->refresh());
                }
            }

            $this->flushCache();

            return $purchaseOrderReceipt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderReceipt $purchaseOrderReceipt): bool
    {
        $timer_start = microtime(true);

        try {
            $hasReturnItemReferences = PurchaseReturnItem::query()
                ->whereIn(
                    'purchase_order_receipt_item_id',
                    $purchaseOrderReceipt->items()->pluck('purchase_order_receipt_items.id')
                )
                ->exists();

            if ($hasReturnItemReferences) {
                throw new Exception('Purchase order receipt cannot be deleted because it already has related purchase return items.');
            }

            $purchaseOrder = $purchaseOrderReceipt->purchaseOrder;

            $journalEntry = $purchaseOrderReceipt->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            foreach ($purchaseOrderReceipt->items as $purchaseOrderReceiptItem) {
                $this->purchaseOrderReceiptItemActions->delete($purchaseOrderReceiptItem);
            }

            foreach ($purchaseOrderReceipt->costs as $purchaseOrderReceiptCost) {
                $this->purchaseOrderReceiptCostActions->delete($purchaseOrderReceiptCost);
            }

            $result = $purchaseOrderReceipt->delete();

            if ($purchaseOrder) {
                PurchaseOrderActions::updateSummary($purchaseOrder->refresh());
            }

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(PurchaseOrderReceipt $purchaseOrderReceipt): void
    {
        $purchaseOrderReceipt->refresh();

        $purchaseOrderReceipt->total_value = (float) $purchaseOrderReceipt->items()->sum('total_value');
        $purchaseOrderReceipt->total_cost = (float) $purchaseOrderReceipt->costs()->sum('amount');
        $purchaseOrderReceipt->save();
    }

    /**
     * P3 — Receipt: Dr inventory / Cr GRNI at header total_value (NET of VAT).
     * Skipped when total_value == 0; an existing entry is deleted when an
     * update brings total_value to 0.
     */
    private function syncJournalEntry(PurchaseOrderReceipt $purchaseOrderReceipt): void
    {
        $journalEntry = $purchaseOrderReceipt->journalEntry;

        if ((float) $purchaseOrderReceipt->total_value > 0) {
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $purchaseOrderReceipt->company_id,
                    branchId: $purchaseOrderReceipt->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $purchaseOrderReceipt->date,
                    journalType: JournalEntryTypeEnum::TRANSACTION->value,
                    sourceType: PurchaseOrderReceipt::class,
                    sourceId: $purchaseOrderReceipt->id,
                    referenceNo: $purchaseOrderReceipt->code,
                    remarks: $purchaseOrderReceipt->remarks,
                    items: $this->buildJournalEntryItems($purchaseOrderReceipt),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $purchaseOrderReceipt->branch_id,
                    code: $journalEntry->code,
                    date: $purchaseOrderReceipt->date,
                    journalType: JournalEntryTypeEnum::TRANSACTION->value,
                    referenceNo: $purchaseOrderReceipt->code,
                    remarks: $purchaseOrderReceipt->remarks,
                    items: $this->buildJournalEntryItems($purchaseOrderReceipt),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }
        } elseif ($journalEntry) {
            $this->journalEntryActions->delete($journalEntry);
        }
    }

    /**
     * @return JournalEntryItemDTO[]
     */
    private function buildJournalEntryItems(PurchaseOrderReceipt $purchaseOrderReceipt): array
    {
        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderReceipt->company->assetCurrentInventoryChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: (float) $purchaseOrderReceipt->total_value,
            credit: 0,
            remarks: $purchaseOrderReceipt->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderReceipt->company->liabilityGoodsReceivedNotInvoicedChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $purchaseOrderReceipt->total_value,
            remarks: $purchaseOrderReceipt->remarks,
        );

        return $items;
    }
}
