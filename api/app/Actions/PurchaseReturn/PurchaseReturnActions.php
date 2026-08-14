<?php

namespace App\Actions\PurchaseReturn;

use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseInvoice\PurchaseInvoiceActions;
use App\Actions\PurchaseReturnItem\PurchaseReturnItemActions;
use App\Actions\PurchaseReturnRefund\PurchaseReturnRefundActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseReturnCreateDTO;
use App\DTOs\PurchaseReturnItemCreateDTO;
use App\DTOs\PurchaseReturnItemUpdateDTO;
use App\DTOs\PurchaseReturnRefundCreateDTO;
use App\DTOs\PurchaseReturnRefundUpdateDTO;
use App\DTOs\PurchaseReturnUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseReturn;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReturnActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseInvoice',
        'warehouse',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseInvoice.supplier',
        'warehouse',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.vatProfile',
        'items.purchaseOrderReceiptItem.purchaseOrderReceipt',
        'items.itemSerials',
        'refunds.cashAccount',
    ];

    public function __construct(
        private readonly PurchaseReturnItemActions $purchaseReturnItemActions,
        private readonly PurchaseReturnRefundActions $purchaseReturnRefundActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?int $supplierId,
        ?int $purchaseInvoiceId,
        ?int $warehouseId,
        ?bool $isSettled,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReturn::select('purchase_returns.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'purchase_returns.company_id')
            ->whereCompanyId('purchase_returns', $companyId)
            ->whereBranchId('purchase_returns', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $supplierId,
            $purchaseInvoiceId,
            $warehouseId,
            $isSettled,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_returns.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_returns.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_returns.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_returns.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($supplierId) {
                $query->where('purchase_returns.supplier_id', $supplierId);
            }

            if ($purchaseInvoiceId) {
                $query->where('purchase_returns.purchase_invoice_id', $purchaseInvoiceId);
            }

            if ($warehouseId) {
                $query->where('purchase_returns.warehouse_id', $warehouseId);
            }

            if (! is_null($isSettled)) {
                $query->where('purchase_returns.is_settled', $isSettled);
            }
        });

        $query->orderBy('purchase_returns.date', 'desc')
            ->orderBy('purchase_returns.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $supplierId ?? '[null]',
                    $purchaseInvoiceId ?? '[null]',
                    $warehouseId ?? '[null]',
                    is_null($isSettled) ? '[null]' : ($isSettled ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_return_'.implode('_', $cacheParams);

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

    public function read(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        return $purchaseReturn->load(self::DETAIL_EAGER_LOADS);
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
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = PurchaseReturn::whereCompanyId('purchase_returns', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PRT'.str_pad($count, 5, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseReturn::whereCompanyId('purchase_returns', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseReturnCreateDTO $data): PurchaseReturn
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturn = new PurchaseReturn();
            $purchaseReturn->company_id = $data->companyId;
            $purchaseReturn->branch_id = $data->branchId;
            $purchaseReturn->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseReturn->date = $this->generateDate($data->date);
            $purchaseReturn->supplier_id = $data->supplierId;
            $purchaseReturn->purchase_invoice_id = $data->purchaseInvoiceId;
            $purchaseReturn->warehouse_id = $data->warehouseId;
            $purchaseReturn->global_discount = $data->globalDiscount;
            $purchaseReturn->rounding = $data->rounding;
            $purchaseReturn->remarks = $data->remarks;
            $purchaseReturn->is_posted = $data->isPosted;
            $purchaseReturn->save();

            foreach ($data->items as $item) {
                $dto = new PurchaseReturnItemCreateDTO(
                    companyId: $purchaseReturn->company_id,
                    branchId: $purchaseReturn->branch_id,
                    purchaseReturnId: $purchaseReturn->id,
                    purchaseOrderReceiptItemId: $item['purchase_order_receipt_item_id'],
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                    priceDiscount: $item['price_discount'],
                    subtotalDiscount: $item['subtotal_discount'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );

                $this->purchaseReturnItemActions->create($dto);
            }

            foreach ($data->refunds as $refund) {
                $dto = new PurchaseReturnRefundCreateDTO(
                    companyId: $purchaseReturn->company_id,
                    branchId: $purchaseReturn->branch_id,
                    purchaseReturnId: $purchaseReturn->id,
                    code: $refund['code'],
                    date: $refund['date'],
                    cashAccountId: $refund['cash_account_id'],
                    amount: $refund['amount'],
                    remarks: $refund['remarks'],
                );

                $this->purchaseReturnRefundActions->create($dto, updateParentSummary: false);
            }

            self::updateSummary($purchaseReturn);

            $this->saveJournalEntry($purchaseReturn);

            if ($purchaseReturn->purchaseInvoice) {
                PurchaseInvoiceActions::updateSummary($purchaseReturn->purchaseInvoice);
            }

            $this->flushCache();

            return $purchaseReturn;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReturn $purchaseReturn, PurchaseReturnUpdateDTO $data): PurchaseReturn
    {
        $timer_start = microtime(true);

        try {
            $previousPurchaseInvoice = $purchaseReturn->purchaseInvoice;

            $purchaseReturn->code = $this->generateUniqueCode($purchaseReturn->company_id, $data->code, $purchaseReturn->id);
            $purchaseReturn->date = $this->generateDate($data->date);
            $purchaseReturn->supplier_id = $data->supplierId;
            $purchaseReturn->purchase_invoice_id = $data->purchaseInvoiceId;
            $purchaseReturn->warehouse_id = $data->warehouseId;
            $purchaseReturn->global_discount = $data->globalDiscount;
            $purchaseReturn->rounding = $data->rounding;
            $purchaseReturn->remarks = $data->remarks;
            $purchaseReturn->is_posted = $data->isPosted;
            $purchaseReturn->save();

            $this->updateItems($purchaseReturn, $data->deleteItemIds, $data->items);
            $this->updateRefunds($purchaseReturn, $data->deleteRefundIds, $data->refunds);

            self::updateSummary($purchaseReturn);

            $this->saveJournalEntry($purchaseReturn);

            if ($previousPurchaseInvoice) {
                PurchaseInvoiceActions::updateSummary($previousPurchaseInvoice->refresh());
            }

            if ($purchaseReturn->purchaseInvoice) {
                if (! $previousPurchaseInvoice || $purchaseReturn->purchaseInvoice->id !== $previousPurchaseInvoice->id) {
                    PurchaseInvoiceActions::updateSummary($purchaseReturn->purchaseInvoice);
                }
            }

            $this->flushCache();

            return $purchaseReturn;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturn $purchaseReturn): bool
    {
        $timer_start = microtime(true);

        try {
            if ($purchaseReturn->refunds()->exists()
                || $purchaseReturn->invoicePayments()->exists()) {
                throw new Exception('Purchase return cannot be deleted because it already has related transactions.');
            }

            $purchaseInvoice = $purchaseReturn->purchaseInvoice;

            foreach ($purchaseReturn->items as $purchaseReturnItem) {
                $this->purchaseReturnItemActions->delete($purchaseReturnItem);
            }

            $journalEntry = $purchaseReturn->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $result = $purchaseReturn->delete();

            if ($purchaseInvoice) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoice->refresh());
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

    public static function updateSummary(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->refresh();

        $purchaseReturn->item_total_before_global_discount = (float) $purchaseReturn->items()->sum('subtotal_after_discount');

        $purchaseReturn->global_discount = min(
            max((float) $purchaseReturn->global_discount, 0),
            (float) $purchaseReturn->item_total_before_global_discount
        );

        foreach ($purchaseReturn->items as $purchaseReturnItem) {
            $purchaseReturnItem->global_discount = (function () use ($purchaseReturnItem, $purchaseReturn) {
                $itemTotalBeforeGlobalDiscount = (float) $purchaseReturn->item_total_before_global_discount;
                $purchaseReturnGlobalDiscount = (float) $purchaseReturn->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $purchaseReturnGlobalDiscount <= 0) {
                    return 0;
                }

                $value = ((float) $purchaseReturnItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $purchaseReturnGlobalDiscount;

                return $value < 0 ? 0 : $value;
            })();
            $purchaseReturnItem->subtotal_after_global_discount = $purchaseReturnItem->subtotal_after_discount - $purchaseReturnItem->global_discount;
            $purchaseReturnItem->save();
        }

        $globalDiscountDifference = round((float) $purchaseReturn->global_discount - (float) $purchaseReturn->items()->sum('global_discount'), 8);
        if (abs($globalDiscountDifference) > 0.00000001) {
            $lastGlobalDiscountItem = $purchaseReturn->items
                ->filter(fn ($purchaseReturnItem) => (float) $purchaseReturnItem->subtotal_after_discount > 0)
                ->last();

            if ($lastGlobalDiscountItem) {
                $lastGlobalDiscountItem->global_discount = max(
                    0,
                    (float) $lastGlobalDiscountItem->global_discount + $globalDiscountDifference
                );
                $lastGlobalDiscountItem->subtotal_after_global_discount = max(
                    0,
                    (float) $lastGlobalDiscountItem->subtotal_after_discount - (float) $lastGlobalDiscountItem->global_discount
                );
                $lastGlobalDiscountItem->save();
            }
        }

        $purchaseReturn->item_total_after_global_discount = (float) $purchaseReturn->items()->sum('subtotal_after_global_discount');

        foreach ($purchaseReturn->items as $purchaseReturnItem) {
            $purchaseReturnItem->vat_base = (function () use ($purchaseReturnItem) {
                $subtotalAfterGlobalDiscount = (float) $purchaseReturnItem->subtotal_after_global_discount;
                $vatRate = (float) $purchaseReturnItem->vat_rate;
                $vatBaseFactor = $purchaseReturnItem->vat_base_denominator > 0
                    ? (float) $purchaseReturnItem->vat_base_numerator / (float) $purchaseReturnItem->vat_base_denominator
                    : 0;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) {
                    return 0;
                }

                if ($purchaseReturnItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();
            $purchaseReturnItem->vat = (function () use ($purchaseReturnItem) {
                $vatBase = (float) $purchaseReturnItem->vat_base;
                $vatRate = (float) $purchaseReturnItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) {
                    return 0;
                }

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();
            $purchaseReturnItem->subtotal_after_vat = (function () use ($purchaseReturnItem) {
                $subtotalAfterGlobalDiscount = (float) $purchaseReturnItem->subtotal_after_global_discount;
                $vat = (float) $purchaseReturnItem->vat;

                if ($purchaseReturnItem->product_unit_is_price_include_vat) {
                    return $subtotalAfterGlobalDiscount;
                }

                return $subtotalAfterGlobalDiscount + $vat;
            })();
            $purchaseReturnItem->save();
        }

        $purchaseReturn->vat_base = (float) $purchaseReturn->items()->sum('vat_base');
        $purchaseReturn->vat = (float) $purchaseReturn->items()->sum('vat');
        $purchaseReturn->item_total_after_vat = (float) $purchaseReturn->items()->sum('subtotal_after_vat');

        foreach ($purchaseReturn->items as $purchaseReturnItem) {
            $purchaseReturnItem->rounding = (function () use ($purchaseReturnItem, $purchaseReturn) {
                $itemSubtotalAfterVat = (float) $purchaseReturnItem->subtotal_after_vat;
                $itemTotalAfterVat = (float) $purchaseReturn->item_total_after_vat;
                $purchaseReturnRounding = (float) $purchaseReturn->rounding;

                if ($itemTotalAfterVat <= 0 || $purchaseReturnRounding == 0 || $itemSubtotalAfterVat <= 0) {
                    return 0;
                }

                return ($itemSubtotalAfterVat / $itemTotalAfterVat) * $purchaseReturnRounding;
            })();
            $purchaseReturnItem->save();
        }

        $roundingDifference = round((float) $purchaseReturn->rounding - (float) $purchaseReturn->items()->sum('rounding'), 8);
        if (abs($roundingDifference) > 0.00000001) {
            $lastRoundingItem = $purchaseReturn->items
                ->filter(fn ($purchaseReturnItem) => (float) $purchaseReturnItem->subtotal_after_vat > 0)
                ->last();

            if ($lastRoundingItem) {
                $lastRoundingItem->rounding = (float) $lastRoundingItem->rounding + $roundingDifference;
                $lastRoundingItem->save();
            }
        }

        foreach ($purchaseReturn->items as $purchaseReturnItem) {
            $purchaseReturnItem->amount_payable = (float) $purchaseReturnItem->subtotal_after_vat
                + (float) $purchaseReturnItem->rounding;
            $purchaseReturnItem->cogs = (function () use ($purchaseReturnItem) {
                $qty = (float) $purchaseReturnItem->qty;
                $netAmount = (float) $purchaseReturnItem->amount_payable - (float) $purchaseReturnItem->vat;

                if ($qty <= 0 || $netAmount <= 0) {
                    return 0;
                }

                return $netAmount / $qty;
            })();
            $purchaseReturnItem->total_cogs = (function () use ($purchaseReturnItem) {
                $qty = (float) $purchaseReturnItem->qty;
                $cogs = (float) $purchaseReturnItem->cogs;

                if ($qty <= 0 || $cogs <= 0) {
                    return 0;
                }

                return $qty * $cogs;
            })();
            $purchaseReturnItem->base_unit_cogs = (function () use ($purchaseReturnItem) {
                $productUnitQtyBase = (float) $purchaseReturnItem->product_unit_qty_base;
                $totalCogs = (float) $purchaseReturnItem->total_cogs;

                if ($productUnitQtyBase <= 0 || $totalCogs <= 0) {
                    return 0;
                }

                return $totalCogs / $productUnitQtyBase;
            })();
            $purchaseReturnItem->save();
        }

        $purchaseReturn->amount_payable = (float) $purchaseReturn->items()->sum('amount_payable');

        $purchaseReturn->amount_allocated_to_invoice = (float) $purchaseReturn->invoicePayments()
            ->where('payment_type', PaymentTypeEnum::RETURN->value)
            ->sum('amount');
        $purchaseReturn->amount_received_total = (float) $purchaseReturn->refunds()->sum('amount');
        $purchaseReturn->amount_settled_total = (float) $purchaseReturn->amount_allocated_to_invoice
            + (float) $purchaseReturn->amount_received_total;
        $purchaseReturn->amount_available = (float) $purchaseReturn->amount_payable
            - (float) $purchaseReturn->amount_settled_total;
        $purchaseReturn->is_settled = (float) $purchaseReturn->amount_payable > 0
            && (float) $purchaseReturn->amount_available == 0;

        $purchaseReturn->save();
    }

    private function updateItems(PurchaseReturn $purchaseReturn, array $deleteIds, array $items): void
    {
        foreach ($deleteIds as $deleteId) {
            $purchaseReturnItem = $purchaseReturn->items()->findOrFail($deleteId);
            $this->purchaseReturnItemActions->delete($purchaseReturnItem);
        }

        foreach ($items as $item) {
            if ($item['id']) {
                $purchaseReturnItem = $purchaseReturn->items()->findOrFail($item['id']);
                $data = new PurchaseReturnItemUpdateDTO(
                    purchaseOrderReceiptItemId: $item['purchase_order_receipt_item_id'],
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                    priceDiscount: $item['price_discount'],
                    subtotalDiscount: $item['subtotal_discount'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'],
                    deleteSerialIds: $item['delete_serial_ids'],
                    serials: $item['serials'],
                );

                $this->purchaseReturnItemActions->update($purchaseReturnItem, $data);
            } else {
                $data = new PurchaseReturnItemCreateDTO(
                    companyId: $purchaseReturn->company_id,
                    branchId: $purchaseReturn->branch_id,
                    purchaseReturnId: $purchaseReturn->id,
                    purchaseOrderReceiptItemId: $item['purchase_order_receipt_item_id'],
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                    priceDiscount: $item['price_discount'],
                    subtotalDiscount: $item['subtotal_discount'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );

                $this->purchaseReturnItemActions->create($data);
            }
        }
    }

    private function updateRefunds(PurchaseReturn $purchaseReturn, array $deleteIds, array $refunds): void
    {
        foreach ($deleteIds as $deleteId) {
            $purchaseReturnRefund = $purchaseReturn->refunds()->findOrFail($deleteId);
            $this->purchaseReturnRefundActions->delete($purchaseReturnRefund, updateParentSummary: false);
        }

        foreach ($refunds as $refund) {
            if ($refund['id']) {
                $purchaseReturnRefund = $purchaseReturn->refunds()->findOrFail($refund['id']);
                $data = new PurchaseReturnRefundUpdateDTO(
                    code: $refund['code'],
                    date: $refund['date'],
                    cashAccountId: $refund['cash_account_id'],
                    amount: $refund['amount'],
                    remarks: $refund['remarks'],
                );

                $this->purchaseReturnRefundActions->update($purchaseReturnRefund, $data, updateParentSummary: false);
            } else {
                $data = new PurchaseReturnRefundCreateDTO(
                    companyId: $purchaseReturn->company_id,
                    branchId: $purchaseReturn->branch_id,
                    purchaseReturnId: $purchaseReturn->id,
                    code: $refund['code'],
                    date: $refund['date'],
                    cashAccountId: $refund['cash_account_id'],
                    amount: $refund['amount'],
                    remarks: $refund['remarks'],
                );

                $this->purchaseReturnRefundActions->create($data, updateParentSummary: false);
            }
        }
    }

    /**
     * Journal P8 — two branches:
     * (a) invoice-linked: Dr supplier AP (amount_payable) / Cr inventory (net goods) + Cr VAT-in (vat).
     * (b) uninvoiced (VAT-free by validation): Dr GRNI (net goods) / Cr inventory (net goods).
     */
    private function saveJournalEntry(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->refresh();

        $amountPayable = (float) $purchaseReturn->amount_payable;
        $vat = (float) $purchaseReturn->vat;
        $netGoods = $amountPayable - $vat;

        $items = [];

        if ($purchaseReturn->purchase_invoice_id) {
            $supplierPayableChartOfAccountId = $purchaseReturn->supplier?->chartOfAccount?->id
                ?? $purchaseReturn->company->liabilityAccountPayableChartOfAccount?->id;

            if ($amountPayable > 0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $supplierPayableChartOfAccountId,
                    sequence: count($items) + 1,
                    debit: $amountPayable,
                    credit: 0,
                    remarks: $purchaseReturn->remarks,
                );
            }

            if ($netGoods > 0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $purchaseReturn->company->assetCurrentInventoryChartOfAccount?->id,
                    sequence: count($items) + 1,
                    debit: 0,
                    credit: $netGoods,
                    remarks: $purchaseReturn->remarks,
                );
            }

            if ($vat > 0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $purchaseReturn->company->assetCurrentVatInChartOfAccount?->id,
                    sequence: count($items) + 1,
                    debit: 0,
                    credit: $vat,
                    remarks: $purchaseReturn->remarks,
                );
            }
        } else {
            if ($netGoods > 0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $purchaseReturn->company->liabilityGoodsReceivedNotInvoicedChartOfAccount?->id,
                    sequence: count($items) + 1,
                    debit: $netGoods,
                    credit: 0,
                    remarks: $purchaseReturn->remarks,
                );

                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $purchaseReturn->company->assetCurrentInventoryChartOfAccount?->id,
                    sequence: count($items) + 1,
                    debit: 0,
                    credit: $netGoods,
                    remarks: $purchaseReturn->remarks,
                );
            }
        }

        $journalEntry = $purchaseReturn->journalEntry;

        if (count($items) === 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        if (! $journalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $purchaseReturn->company_id,
                branchId: $purchaseReturn->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $purchaseReturn->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: PurchaseReturn::class,
                sourceId: $purchaseReturn->id,
                referenceNo: $purchaseReturn->code,
                remarks: $purchaseReturn->remarks,
                items: $items,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $purchaseReturn->branch_id,
                code: $journalEntry->code,
                date: $purchaseReturn->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $purchaseReturn->code,
                remarks: $purchaseReturn->remarks,
                items: $items,
            );
            $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
        }
    }
}
