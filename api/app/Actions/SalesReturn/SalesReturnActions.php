<?php

namespace App\Actions\SalesReturn;

use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesInvoice\SalesInvoiceActions;
use App\Actions\SalesReturnItem\SalesReturnItemActions;
use App\Actions\SalesReturnRefund\SalesReturnRefundActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesReturnCreateDTO;
use App\DTOs\SalesReturnItemCreateDTO;
use App\DTOs\SalesReturnItemUpdateDTO;
use App\DTOs\SalesReturnRefundCreateDTO;
use App\DTOs\SalesReturnRefundUpdateDTO;
use App\DTOs\SalesReturnUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\JournalEntry;
use App\Models\SalesReturn;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesReturnActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'salesInvoice',
        'warehouse',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'salesInvoice',
        'warehouse',
        'items.productUnit.unit',
        'items.productUnit.product.baseProductUnit.unit',
        'items.product',
        'items.vatProfile',
        'items.salesOrderDeliveryItem.salesOrderDelivery',
        'items.itemSerials',
        'refunds.cashAccount',
    ];

    public function __construct(
        private readonly JournalEntryActions $journalEntryActions,
        private readonly SalesReturnItemActions $salesReturnItemActions,
        private readonly SalesReturnRefundActions $salesReturnRefundActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $customerId,
        ?int $salesInvoiceId,
        ?int $warehouseId,
        ?bool $isSettled,
        ?string $startDate,
        ?string $endDate,

        ?ExecuteDTO $execute
    ) {
        $query = SalesReturn::select('sales_returns.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('sales_returns', $companyId)
            ->whereBranchId('sales_returns', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $customerId,
            $salesInvoiceId,
            $warehouseId,
            $isSettled,
            $startDate,
            $endDate,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_returns.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_returns.remarks', 'like', '%'.$search.'%');
                });
            }

            if (! is_null($customerId)) {
                $query->where('sales_returns.customer_id', $customerId);
            }

            if (! is_null($salesInvoiceId)) {
                $query->where('sales_returns.sales_invoice_id', $salesInvoiceId);
            }

            if (! is_null($warehouseId)) {
                $query->where('sales_returns.warehouse_id', $warehouseId);
            }

            if (! is_null($isSettled)) {
                $query->where('sales_returns.is_settled', $isSettled);
            }

            if ($startDate) {
                $query->where('sales_returns.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_returns.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }
        });

        $query->orderBy('sales_returns.date', 'desc');
        $query->orderBy('sales_returns.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $customerId ?? '[null]',
                    $salesInvoiceId ?? '[null]',
                    $warehouseId ?? '[null]',
                    is_null($isSettled) ? '[null]' : ($isSettled ? 'true' : 'false'),
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_return_'.implode('_', $cacheParams);

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

    public function read(SalesReturn $salesReturn): SalesReturn
    {
        return $salesReturn->load(self::DETAIL_EAGER_LOADS);
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
                $count = SalesReturn::whereCompanyId('sales_returns', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'SRT'.str_pad($count, 5, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesReturn::whereCompanyId('sales_returns', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(SalesReturnCreateDTO $data): SalesReturn
    {
        $timer_start = microtime(true);

        try {
            $salesReturn = new SalesReturn();
            $salesReturn->company_id = $data->companyId;
            $salesReturn->branch_id = $data->branchId;
            $salesReturn->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesReturn->date = $this->generateDate($data->date);
            $salesReturn->customer_id = $data->customerId;
            $salesReturn->sales_invoice_id = $data->salesInvoiceId;
            $salesReturn->warehouse_id = $data->warehouseId;
            $salesReturn->global_discount = $data->globalDiscount;
            $salesReturn->rounding = $data->rounding;
            $salesReturn->remarks = $data->remarks;
            $salesReturn->is_posted = $data->isPosted;
            $salesReturn->save();

            foreach ($data->items as $item) {
                $dto = new SalesReturnItemCreateDTO(
                    companyId: $salesReturn->company_id,
                    branchId: $salesReturn->branch_id,
                    salesReturnId: $salesReturn->id,
                    salesOrderDeliveryItemId: $item['sales_order_delivery_item_id'],
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

                $this->salesReturnItemActions->create($dto, false);
            }

            foreach ($data->refunds as $refund) {
                $dto = new SalesReturnRefundCreateDTO(
                    companyId: $salesReturn->company_id,
                    branchId: $salesReturn->branch_id,
                    code: $refund['code'],
                    date: $refund['date'],
                    salesReturnId: $salesReturn->id,
                    cashAccountId: $refund['cash_account_id'],
                    amount: $refund['amount'],
                    remarks: $refund['remarks'],
                );

                $this->salesReturnRefundActions->create($dto, false);
            }

            self::updateSummary($salesReturn);

            $this->syncJournalEntries($salesReturn);

            if ($salesReturn->sales_invoice_id) {
                SalesInvoiceActions::updateSummary($salesReturn->salesInvoice);
            }

            $this->flushCache();

            return $salesReturn;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesReturn $salesReturn, SalesReturnUpdateDTO $data): SalesReturn
    {
        $timer_start = microtime(true);

        try {
            $previousSalesInvoice = $salesReturn->salesInvoice;

            $salesReturn->code = $this->generateUniqueCode($salesReturn->company_id, $data->code, $salesReturn->id);
            $salesReturn->date = $this->generateDate($data->date);
            $salesReturn->customer_id = $data->customerId;
            $salesReturn->sales_invoice_id = $data->salesInvoiceId;
            $salesReturn->warehouse_id = $data->warehouseId;
            $salesReturn->global_discount = $data->globalDiscount;
            $salesReturn->rounding = $data->rounding;
            $salesReturn->remarks = $data->remarks;
            $salesReturn->is_posted = $data->isPosted;
            $salesReturn->save();

            foreach ($data->deleteItemIds as $deleteId) {
                $salesReturnItem = $salesReturn->items()->findOrFail($deleteId);
                $this->salesReturnItemActions->delete($salesReturnItem, false);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $salesReturnItem = $salesReturn->items()->findOrFail($item['id']);
                    $dto = new SalesReturnItemUpdateDTO(
                        salesOrderDeliveryItemId: $item['sales_order_delivery_item_id'],
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

                    $this->salesReturnItemActions->update($salesReturnItem, $dto, false);
                } else {
                    $dto = new SalesReturnItemCreateDTO(
                        companyId: $salesReturn->company_id,
                        branchId: $salesReturn->branch_id,
                        salesReturnId: $salesReturn->id,
                        salesOrderDeliveryItemId: $item['sales_order_delivery_item_id'],
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

                    $this->salesReturnItemActions->create($dto, false);
                }
            }

            foreach ($data->deleteRefundIds as $deleteId) {
                $salesReturnRefund = $salesReturn->refunds()->findOrFail($deleteId);
                $this->salesReturnRefundActions->delete($salesReturnRefund, false);
            }

            foreach ($data->refunds as $refund) {
                if (! empty($refund['id'])) {
                    $salesReturnRefund = $salesReturn->refunds()->findOrFail($refund['id']);
                    $dto = new SalesReturnRefundUpdateDTO(
                        code: $refund['code'],
                        date: $refund['date'],
                        cashAccountId: $refund['cash_account_id'],
                        amount: $refund['amount'],
                        remarks: $refund['remarks'],
                    );

                    $this->salesReturnRefundActions->update($salesReturnRefund, $dto, false);
                } else {
                    $dto = new SalesReturnRefundCreateDTO(
                        companyId: $salesReturn->company_id,
                        branchId: $salesReturn->branch_id,
                        code: $refund['code'],
                        date: $refund['date'],
                        salesReturnId: $salesReturn->id,
                        cashAccountId: $refund['cash_account_id'],
                        amount: $refund['amount'],
                        remarks: $refund['remarks'],
                    );

                    $this->salesReturnRefundActions->create($dto, false);
                }
            }

            self::updateSummary($salesReturn);

            $this->syncJournalEntries($salesReturn);

            if ($previousSalesInvoice) {
                SalesInvoiceActions::updateSummary($previousSalesInvoice->refresh());
            }

            if ($salesReturn->salesInvoice) {
                if (! $previousSalesInvoice || $salesReturn->salesInvoice->id !== $previousSalesInvoice->id) {
                    SalesInvoiceActions::updateSummary($salesReturn->salesInvoice);
                }
            }

            $this->flushCache();

            return $salesReturn;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    /**
     * FROZEN CONTRACT (design §9): totals waterfall (single global_discount
     * value pro-rated with residue-into-last-item, VAT chain, rounding) +
     * settlement roll-ups per design §1.4/§1.5.
     */
    public static function updateSummary(SalesReturn $salesReturn): void
    {
        $salesReturn->refresh();

        $salesReturn->item_total_before_global_discount = $salesReturn->items->sum('subtotal_after_discount');
        $salesReturn->global_discount = min(
            max((float) $salesReturn->global_discount, 0),
            (float) $salesReturn->item_total_before_global_discount
        );

        foreach ($salesReturn->items as $salesReturnItem) {
            $salesReturnItem->global_discount = (function () use ($salesReturnItem, $salesReturn) {
                $itemTotalBeforeGlobalDiscount = (float) $salesReturn->item_total_before_global_discount;
                $salesReturnGlobalDiscount = (float) $salesReturn->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $salesReturnGlobalDiscount <= 0) return 0;

                $value = ((float) $salesReturnItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $salesReturnGlobalDiscount;

                return $value < 0 ? 0 : $value;
            })();
            $salesReturnItem->subtotal_after_global_discount = $salesReturnItem->subtotal_after_discount - $salesReturnItem->global_discount;
            $salesReturnItem->save();
        }

        $globalDiscountDifference = round((float) $salesReturn->global_discount - (float) $salesReturn->items->sum('global_discount'), 8);
        if (abs($globalDiscountDifference) > 0.00000001) {
            $lastGlobalDiscountItem = $salesReturn->items
                ->filter(fn ($salesReturnItem) => (float) $salesReturnItem->subtotal_after_discount > 0)
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

        $salesReturn->item_total_after_global_discount = (float) $salesReturn->items->sum('subtotal_after_global_discount');

        foreach ($salesReturn->items as $salesReturnItem) {
            $salesReturnItem->vat_base = (function () use ($salesReturnItem) {
                $subtotalAfterGlobalDiscount = (float) $salesReturnItem->subtotal_after_global_discount;
                $vatRate = (float) $salesReturnItem->vat_rate;
                $vatBaseFactor = $salesReturnItem->vat_base_denominator > 0
                    ? (float) $salesReturnItem->vat_base_numerator / (float) $salesReturnItem->vat_base_denominator
                    : 0;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) return 0;

                if ($salesReturnItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();
            $salesReturnItem->vat = (function () use ($salesReturnItem) {
                $vatBase = (float) $salesReturnItem->vat_base;
                $vatRate = (float) $salesReturnItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) return 0;

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();
            $salesReturnItem->subtotal_after_vat = (function () use ($salesReturnItem) {
                $subtotalAfterGlobalDiscount = (float) $salesReturnItem->subtotal_after_global_discount;
                $vat = (float) $salesReturnItem->vat;

                if ($salesReturnItem->product_unit_is_price_include_vat) {
                    return $subtotalAfterGlobalDiscount;
                }

                return $subtotalAfterGlobalDiscount + $vat;
            })();
            $salesReturnItem->save();
        }

        $salesReturn->vat_base = (float) $salesReturn->items->sum('vat_base');
        $salesReturn->vat = (float) $salesReturn->items->sum('vat');
        $salesReturn->item_total_after_vat = (float) $salesReturn->items->sum('subtotal_after_vat');

        foreach ($salesReturn->items as $salesReturnItem) {
            $salesReturnItem->rounding = (function () use ($salesReturnItem, $salesReturn) {
                $itemSubtotalAfterVat = (float) $salesReturnItem->subtotal_after_vat;
                $itemTotalAfterVat = (float) $salesReturn->item_total_after_vat;
                $salesReturnRounding = (float) $salesReturn->rounding;

                if ($itemTotalAfterVat <= 0 || $salesReturnRounding == 0 || $itemSubtotalAfterVat <= 0) return 0;

                return ($itemSubtotalAfterVat / $itemTotalAfterVat) * $salesReturnRounding;
            })();
            $salesReturnItem->amount_payable = (float) $salesReturnItem->subtotal_after_vat + (float) $salesReturnItem->rounding;
            $salesReturnItem->save();
        }

        $roundingDifference = round((float) $salesReturn->rounding - (float) $salesReturn->items->sum('rounding'), 8);
        if (abs($roundingDifference) > 0.00000001) {
            $lastRoundingItem = $salesReturn->items
                ->filter(fn ($salesReturnItem) => (float) $salesReturnItem->subtotal_after_vat > 0)
                ->last();

            if ($lastRoundingItem) {
                $lastRoundingItem->rounding = (float) $lastRoundingItem->rounding + $roundingDifference;
                $lastRoundingItem->amount_payable = (float) $lastRoundingItem->subtotal_after_vat
                    + (float) $lastRoundingItem->rounding;
                $lastRoundingItem->save();
            }
        }

        $salesReturn->amount_payable = (float) $salesReturn->items->sum('amount_payable');

        $salesReturn->amount_allocated_to_invoice = (float) $salesReturn->invoicePayments()
            ->where('payment_type', PaymentTypeEnum::RETURN->value)
            ->sum('amount');
        $salesReturn->amount_received_total = (float) $salesReturn->refunds()->sum('amount');
        $salesReturn->amount_settled_total = (float) $salesReturn->amount_allocated_to_invoice
            + (float) $salesReturn->amount_received_total;
        $salesReturn->amount_available = (float) $salesReturn->amount_payable
            - (float) $salesReturn->amount_settled_total;
        $salesReturn->is_settled = (float) $salesReturn->amount_payable > 0
            && round((float) $salesReturn->amount_available, 8) == 0.0;

        $salesReturn->save();
    }

    public function delete(SalesReturn $salesReturn): bool
    {
        $timer_start = microtime(true);

        try {
            if (
                $salesReturn->refunds()->exists()
                || $salesReturn->invoicePayments()->exists()
            ) {
                throw new Exception('Sales return cannot be deleted because it already has related transactions.');
            }

            $salesInvoice = $salesReturn->salesInvoice;

            foreach ($salesReturn->items as $salesReturnItem) {
                $this->salesReturnItemActions->delete($salesReturnItem, false);
            }

            $journalEntry = $salesReturn->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $currentMonthEarningsJournalEntry = $salesReturn->currentMonthEarningsJournalEntry;
            if ($currentMonthEarningsJournalEntry) $this->journalEntryActions->delete($currentMonthEarningsJournalEntry);

            $monthEndClosingJournalEntry = $salesReturn->monthEndClosingJournalEntry;
            if ($monthEndClosingJournalEntry) $this->journalEntryActions->delete($monthEndClosingJournalEntry);

            $monthToYearClosingJournalEntry = $salesReturn->monthToYearClosingJournalEntry;
            if ($monthToYearClosingJournalEntry) $this->journalEntryActions->delete($monthToYearClosingJournalEntry);

            $yearToRetainedEarningsClosingJournalEntry = $salesReturn->yearToRetainedEarningsClosingJournalEntry;
            if ($yearToRetainedEarningsClosingJournalEntry) $this->journalEntryActions->delete($yearToRetainedEarningsClosingJournalEntry);

            $result = $salesReturn->delete();

            if ($salesInvoice) {
                SalesInvoiceActions::updateSummary($salesInvoice->refresh());
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

    /**
     * Journals per design §5 S8 + §5.1 closing set with the two-component
     * merge: contra-revenue (net_goods, expense-direction) + COGS reversal
     * (total_cogs, income-direction). Zero-amount lines are omitted; entries
     * whose lines all become 0 are deleted (design §0.12).
     */
    private function syncJournalEntries(SalesReturn $salesReturn): void
    {
        $salesReturn->refresh();

        $company = $salesReturn->company;
        $amountPayable = (float) $salesReturn->amount_payable;
        $vat = (float) $salesReturn->vat;
        $netGoods = $amountPayable - $vat;
        $totalCogs = (float) $salesReturn->items()->sum('total_cogs');
        $isInvoiced = ! is_null($salesReturn->sales_invoice_id);

        $documentDate = ($salesReturn->date instanceof Carbon
            ? $salesReturn->date->copy()
            : Carbon::parse((string) $salesReturn->date))
            ->format('Y-m-d H:i:s');
        $endOfMonthDate = ($salesReturn->date instanceof Carbon
            ? $salesReturn->date->copy()
            : Carbon::parse((string) $salesReturn->date))
            ->endOfMonth()
            ->format('Y-m-d H:i:s');
        $endOfYearDate = ($salesReturn->date instanceof Carbon
            ? $salesReturn->date->copy()
            : Carbon::parse((string) $salesReturn->date))
            ->endOfYear()
            ->format('Y-m-d H:i:s');

        // TRANSACTION (S8): branch (a) invoice-linked posts revenue reversal +
        // VAT + AR AND the COGS reversal in one entry; branch (b) goods-only.
        $transactionItems = [];
        if ($isInvoiced) {
            if ($netGoods > 0) {
                $transactionItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->incomeSalesReturnChartOfAccount?->id,
                    sequence: count($transactionItems) + 1,
                    debit: $netGoods,
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
            }

            if ($vat > 0) {
                $transactionItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->liabilityTaxPayableChartOfAccount?->id,
                    sequence: count($transactionItems) + 1,
                    debit: $vat,
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
            }

            if ($amountPayable > 0) {
                $transactionItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $salesReturn->customer?->chartOfAccount?->id
                        ?? $company->assetCurrentAccountReceivableChartOfAccount?->id,
                    sequence: count($transactionItems) + 1,
                    debit: 0,
                    credit: $amountPayable,
                    remarks: $salesReturn->remarks,
                );
            }
        }

        if ($totalCogs > 0) {
            $transactionItems[] = new JournalEntryItemDTO(
                chartOfAccountId: $company->assetCurrentInventoryChartOfAccount?->id,
                sequence: count($transactionItems) + 1,
                debit: $totalCogs,
                credit: 0,
                remarks: $salesReturn->remarks,
            );

            $transactionItems[] = new JournalEntryItemDTO(
                chartOfAccountId: $company->cogsGoodsSoldChartOfAccount?->id,
                sequence: count($transactionItems) + 1,
                debit: 0,
                credit: $totalCogs,
                remarks: $salesReturn->remarks,
            );
        }

        $this->syncJournalEntry(
            salesReturn: $salesReturn,
            existingJournalEntry: $salesReturn->journalEntry,
            journalType: JournalEntryTypeEnum::TRANSACTION->value,
            date: $documentDate,
            items: $transactionItems,
        );

        // Closing set (§5.1). P&L components:
        // - contra-revenue net_goods, expense-direction (branch a only)
        // - COGS reversal total_cogs, income-direction
        $expenseComponent = $isInvoiced && $netGoods > 0 ? $netGoods : 0.0;
        $incomeComponent = $totalCogs;
        $net = $incomeComponent - $expenseComponent;

        $currentMonthEarningsItems = [];
        if (abs($net) > 0) {
            if ($net >= 0) {
                $currentMonthEarningsItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                    sequence: 1,
                    debit: $net,
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
                $currentMonthEarningsItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $net,
                    remarks: $salesReturn->remarks,
                );
            } else {
                $currentMonthEarningsItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: abs($net),
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
                $currentMonthEarningsItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: abs($net),
                    remarks: $salesReturn->remarks,
                );
            }
        }

        $this->syncJournalEntry(
            salesReturn: $salesReturn,
            existingJournalEntry: $salesReturn->currentMonthEarningsJournalEntry,
            journalType: JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
            date: $documentDate,
            items: $currentMonthEarningsItems,
        );

        // MONTH_END_CLOSING: reverse every P&L line against suspense.
        $monthEndClosingItems = [];
        if ($expenseComponent > 0) {
            $monthEndClosingItems[] = new JournalEntryItemDTO(
                chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                sequence: count($monthEndClosingItems) + 1,
                debit: $expenseComponent,
                credit: 0,
                remarks: $salesReturn->remarks,
            );
            $monthEndClosingItems[] = new JournalEntryItemDTO(
                chartOfAccountId: $company->incomeSalesReturnChartOfAccount?->id,
                sequence: count($monthEndClosingItems) + 1,
                debit: 0,
                credit: $expenseComponent,
                remarks: $salesReturn->remarks,
            );
        }
        if ($incomeComponent > 0) {
            $monthEndClosingItems[] = new JournalEntryItemDTO(
                chartOfAccountId: $company->cogsGoodsSoldChartOfAccount?->id,
                sequence: count($monthEndClosingItems) + 1,
                debit: $incomeComponent,
                credit: 0,
                remarks: $salesReturn->remarks,
            );
            $monthEndClosingItems[] = new JournalEntryItemDTO(
                chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                sequence: count($monthEndClosingItems) + 1,
                debit: 0,
                credit: $incomeComponent,
                remarks: $salesReturn->remarks,
            );
        }

        $this->syncJournalEntry(
            salesReturn: $salesReturn,
            existingJournalEntry: $salesReturn->monthEndClosingJournalEntry,
            journalType: JournalEntryTypeEnum::MONTH_END_CLOSING->value,
            date: $endOfMonthDate,
            items: $monthEndClosingItems,
        );

        // MONTH_TO_YEAR_CLOSING: CME -> CYE for net (same sign logic as CME).
        $monthToYearClosingItems = [];
        if (abs($net) > 0) {
            if ($net >= 0) {
                $monthToYearClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $net,
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
                $monthToYearClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $net,
                    remarks: $salesReturn->remarks,
                );
            } else {
                $monthToYearClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: abs($net),
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
                $monthToYearClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: abs($net),
                    remarks: $salesReturn->remarks,
                );
            }
        }

        $this->syncJournalEntry(
            salesReturn: $salesReturn,
            existingJournalEntry: $salesReturn->monthToYearClosingJournalEntry,
            journalType: JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
            date: $endOfMonthDate,
            items: $monthToYearClosingItems,
        );

        // YEAR_TO_RETAINED_EARNINGS_CLOSING: CYE -> RE for net.
        $yearToRetainedEarningsClosingItems = [];
        if (abs($net) > 0) {
            if ($net >= 0) {
                $yearToRetainedEarningsClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $net,
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
                $yearToRetainedEarningsClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityRetainedEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $net,
                    remarks: $salesReturn->remarks,
                );
            } else {
                $yearToRetainedEarningsClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityRetainedEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: abs($net),
                    credit: 0,
                    remarks: $salesReturn->remarks,
                );
                $yearToRetainedEarningsClosingItems[] = new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: abs($net),
                    remarks: $salesReturn->remarks,
                );
            }
        }

        $this->syncJournalEntry(
            salesReturn: $salesReturn,
            existingJournalEntry: $salesReturn->yearToRetainedEarningsClosingJournalEntry,
            journalType: JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
            date: $endOfYearDate,
            items: $yearToRetainedEarningsClosingItems,
        );
    }

    private function syncJournalEntry(
        SalesReturn $salesReturn,
        ?JournalEntry $existingJournalEntry,
        string $journalType,
        string $date,
        array $items,
    ): void {
        if (count($items) === 0) {
            if ($existingJournalEntry) {
                $this->journalEntryActions->delete($existingJournalEntry);
            }

            return;
        }

        if (! $existingJournalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $salesReturn->company_id,
                branchId: $salesReturn->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $date,
                journalType: $journalType,
                sourceType: SalesReturn::class,
                sourceId: $salesReturn->id,
                referenceNo: $salesReturn->code,
                remarks: $salesReturn->remarks,
                items: $items,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $salesReturn->branch_id,
                code: $existingJournalEntry->code,
                date: $date,
                journalType: $journalType,
                referenceNo: $salesReturn->code,
                remarks: $salesReturn->remarks,
                items: $items,
            );
            $this->journalEntryActions->update($existingJournalEntry, $journalEntryDTO);
        }
    }
}
