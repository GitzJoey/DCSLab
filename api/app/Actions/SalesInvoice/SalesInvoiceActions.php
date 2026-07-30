<?php

namespace App\Actions\SalesInvoice;

use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesInvoiceItem\SalesInvoiceItemActions;
use App\Actions\SalesInvoicePayment\SalesInvoicePaymentActions;
use App\Actions\SalesOrder\SalesOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesInvoiceCreateDTO;
use App\DTOs\SalesInvoiceItemCreateDTO;
use App\DTOs\SalesInvoiceItemUpdateDTO;
use App\DTOs\SalesInvoicePaymentCreateDTO;
use App\DTOs\SalesInvoicePaymentUpdateDTO;
use App\DTOs\SalesInvoiceUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesInvoiceActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'salesOrder',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'salesOrder.customer',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.vatProfile',
        'items.salesOrderItem',
        'payments.cashAccount',
        'payments.salesOrderPayment',
        'payments.salesReturn',
        'salesReturns.customer',
    ];

    public function __construct(
        private readonly SalesInvoiceItemActions $salesInvoiceItemActions,
        private readonly SalesInvoicePaymentActions $salesInvoicePaymentActions,
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
        ?int $customerId,
        ?int $salesOrderId,
        ?bool $isPosted,
        ?bool $isPaidOff,

        ?ExecuteDTO $execute
    ) {
        $query = SalesInvoice::select('sales_invoices.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('sales_invoices', $companyId)
            ->whereBranchId('sales_invoices', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $customerId,
            $salesOrderId,
            $isPosted,
            $isPaidOff,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_invoices.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_invoices.tax_invoice_number', 'like', '%'.$search.'%')
                        ->orWhere('sales_invoices.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('sales_invoices.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_invoices.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($customerId) {
                $query->where('sales_invoices.customer_id', $customerId);
            }

            if ($salesOrderId) {
                $query->where('sales_invoices.sales_order_id', $salesOrderId);
            }

            if (! is_null($isPosted)) {
                $query->where('sales_invoices.is_posted', $isPosted);
            }

            if (! is_null($isPaidOff)) {
                $query->where('sales_invoices.is_paid_off', $isPaidOff);
            }
        });

        $query->orderBy('sales_invoices.date', 'desc')
            ->orderBy('sales_invoices.id', 'asc');

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
                    $customerId ?? '[null]',
                    $salesOrderId ?? '[null]',
                    is_null($isPosted) ? '[null]' : ($isPosted ? 'true' : 'false'),
                    is_null($isPaidOff) ? '[null]' : ($isPaidOff ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_invoice_'.implode('_', $cacheParams);

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

    public function read(SalesInvoice $salesInvoice): SalesInvoice
    {
        return $salesInvoice->load(self::DETAIL_EAGER_LOADS);
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
                $count = SalesInvoice::whereCompanyId('sales_invoices', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'SI'.str_pad($count, 5, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesInvoice::whereCompanyId('sales_invoices', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(SalesInvoiceCreateDTO $data): SalesInvoice
    {
        $timer_start = microtime(true);

        try {
            $salesInvoice = new SalesInvoice();
            $salesInvoice->company_id = $data->companyId;
            $salesInvoice->branch_id = $data->branchId;
            $salesInvoice->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesInvoice->date = $this->generateDate($data->date);
            $salesInvoice->due_days = $data->dueDays;
            $salesInvoice->customer_id = $data->customerId;
            $salesInvoice->sales_order_id = $data->salesOrderId;
            $salesInvoice->tax_invoice_number = $data->taxInvoiceNumber;
            $salesInvoice->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $salesInvoice->tax_invoice_vat = $data->taxInvoiceVat;
            $salesInvoice->remarks = $data->remarks;
            $salesInvoice->is_posted = $data->isPosted;
            $salesInvoice->global_discount = $data->globalDiscount;
            $salesInvoice->rounding = $data->rounding;
            $salesInvoice->save();

            foreach ($data->items as $item) {
                $dto = new SalesInvoiceItemCreateDTO(
                    companyId: $salesInvoice->company_id,
                    branchId: $salesInvoice->branch_id,
                    salesInvoiceId: $salesInvoice->id,
                    salesOrderItemId: $item['sales_order_item_id'] ?? null,
                    qty: (float) $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: (float) $item['product_unit_conversion_value'],
                    productUnitPrice: (float) $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: (bool) $item['product_unit_is_price_include_vat'],
                    priceDiscount: (float) $item['price_discount'],
                    subtotalDiscount: (float) $item['subtotal_discount'],
                    vatProfileId: $item['vat_profile_id'] ?? null,
                    vatRate: (float) $item['vat_rate'],
                    vatBaseNumerator: (int) $item['vat_base_numerator'],
                    vatBaseDenominator: (int) $item['vat_base_denominator'],
                    remarks: $item['remarks'] ?? null,
                );
                $this->salesInvoiceItemActions->create($dto, false);
            }

            foreach ($data->payments as $payment) {
                $dto = new SalesInvoicePaymentCreateDTO(
                    companyId: $salesInvoice->company_id,
                    branchId: $salesInvoice->branch_id,
                    salesInvoiceId: $salesInvoice->id,
                    code: $payment['code'],
                    date: $payment['date'],
                    paymentType: $payment['payment_type'],
                    cashAccountId: $payment['cash_account_id'] ?? null,
                    salesOrderPaymentId: $payment['sales_order_payment_id'] ?? null,
                    salesReturnId: $payment['sales_return_id'] ?? null,
                    amount: (float) $payment['amount'],
                    remarks: $payment['remarks'] ?? null,
                );
                $this->salesInvoicePaymentActions->create($dto, false);
            }

            self::updateSummary($salesInvoice);

            $this->upsertJournalEntries($salesInvoice);

            $this->flushCache();

            return $salesInvoice;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesInvoice $salesInvoice, SalesInvoiceUpdateDTO $data): SalesInvoice
    {
        $timer_start = microtime(true);

        try {
            $previousSalesOrder = $salesInvoice->salesOrder;

            $salesInvoice->code = $this->generateUniqueCode($salesInvoice->company_id, $data->code, $salesInvoice->id);
            $salesInvoice->date = $this->generateDate($data->date);
            $salesInvoice->due_days = $data->dueDays;
            $salesInvoice->customer_id = $data->customerId;
            $salesInvoice->sales_order_id = $data->salesOrderId;
            $salesInvoice->tax_invoice_number = $data->taxInvoiceNumber;
            $salesInvoice->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $salesInvoice->tax_invoice_vat = $data->taxInvoiceVat;
            $salesInvoice->remarks = $data->remarks;
            $salesInvoice->is_posted = $data->isPosted;
            $salesInvoice->global_discount = $data->globalDiscount;
            $salesInvoice->rounding = $data->rounding;
            $salesInvoice->save();

            foreach ($data->deleteItemIds as $deleteItemId) {
                $salesInvoiceItem = $salesInvoice->items()->findOrFail($deleteItemId);
                $this->salesInvoiceItemActions->delete($salesInvoiceItem, false);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $salesInvoiceItem = $salesInvoice->items()->findOrFail($item['id']);
                    $dto = new SalesInvoiceItemUpdateDTO(
                        salesOrderItemId: $item['sales_order_item_id'] ?? null,
                        qty: (float) $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: (float) $item['product_unit_conversion_value'],
                        productUnitPrice: (float) $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: (bool) $item['product_unit_is_price_include_vat'],
                        priceDiscount: (float) $item['price_discount'],
                        subtotalDiscount: (float) $item['subtotal_discount'],
                        vatProfileId: $item['vat_profile_id'] ?? null,
                        vatRate: (float) $item['vat_rate'],
                        vatBaseNumerator: (int) $item['vat_base_numerator'],
                        vatBaseDenominator: (int) $item['vat_base_denominator'],
                        remarks: $item['remarks'] ?? null,
                    );
                    $this->salesInvoiceItemActions->update($salesInvoiceItem, $dto, false);
                } else {
                    $dto = new SalesInvoiceItemCreateDTO(
                        companyId: $salesInvoice->company_id,
                        branchId: $salesInvoice->branch_id,
                        salesInvoiceId: $salesInvoice->id,
                        salesOrderItemId: $item['sales_order_item_id'] ?? null,
                        qty: (float) $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: (float) $item['product_unit_conversion_value'],
                        productUnitPrice: (float) $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: (bool) $item['product_unit_is_price_include_vat'],
                        priceDiscount: (float) $item['price_discount'],
                        subtotalDiscount: (float) $item['subtotal_discount'],
                        vatProfileId: $item['vat_profile_id'] ?? null,
                        vatRate: (float) $item['vat_rate'],
                        vatBaseNumerator: (int) $item['vat_base_numerator'],
                        vatBaseDenominator: (int) $item['vat_base_denominator'],
                        remarks: $item['remarks'] ?? null,
                    );
                    $this->salesInvoiceItemActions->create($dto, false);
                }
            }

            foreach ($data->deletePaymentIds as $deletePaymentId) {
                $salesInvoicePayment = $salesInvoice->payments()->findOrFail($deletePaymentId);
                $this->salesInvoicePaymentActions->delete($salesInvoicePayment, false);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $salesInvoicePayment = $salesInvoice->payments()->findOrFail($payment['id']);
                    $dto = new SalesInvoicePaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        paymentType: $payment['payment_type'],
                        cashAccountId: $payment['cash_account_id'] ?? null,
                        salesOrderPaymentId: $payment['sales_order_payment_id'] ?? null,
                        salesReturnId: $payment['sales_return_id'] ?? null,
                        amount: (float) $payment['amount'],
                        remarks: $payment['remarks'] ?? null,
                    );
                    $this->salesInvoicePaymentActions->update($salesInvoicePayment, $dto, false);
                } else {
                    $dto = new SalesInvoicePaymentCreateDTO(
                        companyId: $salesInvoice->company_id,
                        branchId: $salesInvoice->branch_id,
                        salesInvoiceId: $salesInvoice->id,
                        code: $payment['code'],
                        date: $payment['date'],
                        paymentType: $payment['payment_type'],
                        cashAccountId: $payment['cash_account_id'] ?? null,
                        salesOrderPaymentId: $payment['sales_order_payment_id'] ?? null,
                        salesReturnId: $payment['sales_return_id'] ?? null,
                        amount: (float) $payment['amount'],
                        remarks: $payment['remarks'] ?? null,
                    );
                    $this->salesInvoicePaymentActions->create($dto, false);
                }
            }

            self::updateSummary($salesInvoice);

            if ($previousSalesOrder && (int) $previousSalesOrder->id !== (int) $salesInvoice->sales_order_id) {
                SalesOrderActions::updateSummary($previousSalesOrder->refresh());
            }

            $this->upsertJournalEntries($salesInvoice);

            $this->flushCache();

            return $salesInvoice;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(SalesInvoice $salesInvoice): void
    {
        $salesInvoice->refresh();

        $salesInvoice->item_total_before_global_discount = (float) $salesInvoice->items()->sum('subtotal_after_discount');
        $salesInvoice->global_discount = min(
            max((float) $salesInvoice->global_discount, 0),
            (float) $salesInvoice->item_total_before_global_discount
        );

        foreach ($salesInvoice->items as $salesInvoiceItem) {
            $salesInvoiceItem->global_discount = (function () use ($salesInvoiceItem, $salesInvoice) {
                $itemTotalBeforeGlobalDiscount = (float) $salesInvoice->item_total_before_global_discount;
                $salesInvoiceGlobalDiscount = (float) $salesInvoice->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $salesInvoiceGlobalDiscount <= 0) {
                    return 0;
                }

                $value = ((float) $salesInvoiceItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $salesInvoiceGlobalDiscount;

                return $value < 0 ? 0 : $value;
            })();
            $salesInvoiceItem->subtotal_after_global_discount = (float) $salesInvoiceItem->subtotal_after_discount - (float) $salesInvoiceItem->global_discount;
            $salesInvoiceItem->save();
        }

        $globalDiscountDifference = round((float) $salesInvoice->global_discount - (float) $salesInvoice->items()->sum('global_discount'), 8);
        if (abs($globalDiscountDifference) > 0.00000001) {
            $lastGlobalDiscountSalesInvoiceItem = $salesInvoice->items
                ->filter(fn ($salesInvoiceItem) => (float) $salesInvoiceItem->subtotal_after_discount > 0)
                ->last();

            if ($lastGlobalDiscountSalesInvoiceItem) {
                $lastGlobalDiscountSalesInvoiceItem->global_discount = max(
                    0,
                    (float) $lastGlobalDiscountSalesInvoiceItem->global_discount + $globalDiscountDifference
                );
                $lastGlobalDiscountSalesInvoiceItem->subtotal_after_global_discount = max(
                    0,
                    (float) $lastGlobalDiscountSalesInvoiceItem->subtotal_after_discount - (float) $lastGlobalDiscountSalesInvoiceItem->global_discount
                );
                $lastGlobalDiscountSalesInvoiceItem->save();
            }
        }

        $salesInvoice->item_total_after_global_discount = (float) $salesInvoice->items()->sum('subtotal_after_global_discount');

        foreach ($salesInvoice->items as $salesInvoiceItem) {
            $salesInvoiceItem->vat_base = (function () use ($salesInvoiceItem) {
                $subtotalAfterGlobalDiscount = (float) $salesInvoiceItem->subtotal_after_global_discount;
                $vatRate = (float) $salesInvoiceItem->vat_rate;
                $vatBaseFactor = $salesInvoiceItem->vat_base_denominator > 0
                    ? (float) $salesInvoiceItem->vat_base_numerator / (float) $salesInvoiceItem->vat_base_denominator
                    : 0;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) {
                    return 0;
                }

                if ($salesInvoiceItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();
            $salesInvoiceItem->vat = (function () use ($salesInvoiceItem) {
                $vatBase = (float) $salesInvoiceItem->vat_base;
                $vatRate = (float) $salesInvoiceItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) {
                    return 0;
                }

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();
            $salesInvoiceItem->subtotal_after_vat = (function () use ($salesInvoiceItem) {
                $subtotalAfterGlobalDiscount = (float) $salesInvoiceItem->subtotal_after_global_discount;
                $vat = (float) $salesInvoiceItem->vat;

                if ($salesInvoiceItem->product_unit_is_price_include_vat) {
                    return $subtotalAfterGlobalDiscount;
                }

                return $subtotalAfterGlobalDiscount + $vat;
            })();
            $salesInvoiceItem->save();
        }

        $salesInvoice->vat_base = (float) $salesInvoice->items()->sum('vat_base');
        $salesInvoice->vat = (float) $salesInvoice->items()->sum('vat');
        $salesInvoice->item_total_after_vat = (float) $salesInvoice->items()->sum('subtotal_after_vat');

        foreach ($salesInvoice->items as $salesInvoiceItem) {
            $salesInvoiceItem->rounding = (function () use ($salesInvoiceItem, $salesInvoice) {
                $salesInvoiceItemSubtotalAfterVat = (float) $salesInvoiceItem->subtotal_after_vat;
                $itemTotalAfterVat = (float) $salesInvoice->item_total_after_vat;
                $salesInvoiceRounding = (float) $salesInvoice->rounding;

                if ($itemTotalAfterVat <= 0 || $salesInvoiceRounding == 0 || $salesInvoiceItemSubtotalAfterVat <= 0) {
                    return 0;
                }

                return ($salesInvoiceItemSubtotalAfterVat / $itemTotalAfterVat) * $salesInvoiceRounding;
            })();
            $salesInvoiceItem->save();
        }

        $roundingDifference = round((float) $salesInvoice->rounding - (float) $salesInvoice->items()->sum('rounding'), 8);
        if (abs($roundingDifference) > 0.00000001) {
            $lastRoundingSalesInvoiceItem = $salesInvoice->items
                ->filter(fn ($salesInvoiceItem) => (float) $salesInvoiceItem->subtotal_after_vat > 0)
                ->last();

            if ($lastRoundingSalesInvoiceItem) {
                $lastRoundingSalesInvoiceItem->rounding = (float) $lastRoundingSalesInvoiceItem->rounding + $roundingDifference;
                $lastRoundingSalesInvoiceItem->save();
            }
        }

        foreach ($salesInvoice->items as $salesInvoiceItem) {
            $salesInvoiceItem->amount_payable = (float) $salesInvoiceItem->subtotal_after_vat
                + (float) $salesInvoiceItem->rounding;
            $salesInvoiceItem->save();
        }

        $salesInvoice->amount_payable = (float) $salesInvoice->items()->sum('amount_payable');
        $salesInvoice->amount_paid_down_payment = (float) $salesInvoice->payments()
            ->where('payment_type', PaymentTypeEnum::DOWN_PAYMENT->value)
            ->sum('amount');
        $salesInvoice->amount_paid_return = (float) $salesInvoice->payments()
            ->where('payment_type', PaymentTypeEnum::RETURN->value)
            ->sum('amount');
        $salesInvoice->amount_paid_total =
            (float) $salesInvoice->amount_paid_down_payment +
            (float) $salesInvoice->amount_paid_return +
            (float) $salesInvoice->payments()
                ->where('payment_type', PaymentTypeEnum::CASH->value)
                ->sum('amount');
        $salesInvoice->amount_due = max(0, (float) $salesInvoice->amount_payable - (float) $salesInvoice->amount_paid_total);
        $salesInvoice->is_paid_off = $salesInvoice->amount_due == 0;

        $salesInvoice->save();

        $salesOrder = $salesInvoice->salesOrder;
        if ($salesOrder) {
            SalesOrderActions::updateSummary($salesOrder);
        }
    }

    public function delete(SalesInvoice $salesInvoice): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            if ($salesInvoice->payments()->exists() || $salesInvoice->salesReturns()->exists()) {
                throw new Exception('Sales invoice cannot be deleted because it already has related transactions.');
            }

            foreach ($salesInvoice->items as $salesInvoiceItem) {
                $this->salesInvoiceItemActions->delete($salesInvoiceItem, false);
            }

            $journalEntry = $salesInvoice->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $currentMonthEarningsJournalEntry = $salesInvoice->currentMonthEarningsJournalEntry;
            if ($currentMonthEarningsJournalEntry) {
                $this->journalEntryActions->delete($currentMonthEarningsJournalEntry);
            }

            $monthEndClosingJournalEntry = $salesInvoice->monthEndClosingJournalEntry;
            if ($monthEndClosingJournalEntry) {
                $this->journalEntryActions->delete($monthEndClosingJournalEntry);
            }

            $monthToYearClosingJournalEntry = $salesInvoice->monthToYearClosingJournalEntry;
            if ($monthToYearClosingJournalEntry) {
                $this->journalEntryActions->delete($monthToYearClosingJournalEntry);
            }

            $yearToRetainedEarningsClosingJournalEntry = $salesInvoice->yearToRetainedEarningsClosingJournalEntry;
            if ($yearToRetainedEarningsClosingJournalEntry) {
                $this->journalEntryActions->delete($yearToRetainedEarningsClosingJournalEntry);
            }

            $salesOrder = $salesInvoice->salesOrder;

            $retval = $salesInvoice->delete();

            if ($salesOrder) {
                SalesOrderActions::updateSummary($salesOrder);
            }

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    /**
     * S5 journal + full closing set (income direction, design section 5/5.1).
     *
     * TRANSACTION: Dr customer AR (amount_payable) / Cr income sales (net_goods) + Cr tax payable (vat).
     * net_goods = amount_payable - vat. Zero-amount lines are omitted; entries whose lines
     * are all zero are skipped on create and deleted on update (design section 0.12).
     */
    private function upsertJournalEntries(SalesInvoice $salesInvoice): void
    {
        $salesInvoice->refresh();

        $amountPayable = (float) $salesInvoice->amount_payable;
        $vat = (float) $salesInvoice->vat;
        $netGoods = $amountPayable - $vat;

        $customerAccountReceivableChartOfAccountId = $salesInvoice->customer?->chartOfAccount?->id
            ?? $salesInvoice->company->assetCurrentAccountReceivableChartOfAccount?->id;
        $incomeSalesChartOfAccountId = $salesInvoice->company->incomeSalesChartOfAccount?->id;
        $liabilityTaxPayableChartOfAccountId = $salesInvoice->company->liabilityTaxPayableChartOfAccount?->id;
        $systemSuspenseChartOfAccountId = $salesInvoice->company->systemSuspenseChartOfAccount?->id;
        $equityCurrentMonthEarningsChartOfAccountId = $salesInvoice->company->equityCurrentMonthEarningsChartOfAccount?->id;
        $equityCurrentYearEarningsChartOfAccountId = $salesInvoice->company->equityCurrentYearEarningsChartOfAccount?->id;
        $equityRetainedEarningsChartOfAccountId = $salesInvoice->company->equityRetainedEarningsChartOfAccount?->id;

        $transactionDate = $salesInvoice->date;
        $endOfMonthDate = (function () use ($salesInvoice) {
            return ($salesInvoice->date instanceof Carbon
                ? $salesInvoice->date->copy()
                : Carbon::parse((string) $salesInvoice->date))
                ->endOfMonth()
                ->format('Y-m-d H:i:s');
        })();
        $endOfYearDate = (function () use ($salesInvoice) {
            return ($salesInvoice->date instanceof Carbon
                ? $salesInvoice->date->copy()
                : Carbon::parse((string) $salesInvoice->date))
                ->endOfYear()
                ->format('Y-m-d H:i:s');
        })();

        $transactionItems = (function () use (
            $salesInvoice,
            $amountPayable,
            $vat,
            $netGoods,
            $customerAccountReceivableChartOfAccountId,
            $incomeSalesChartOfAccountId,
            $liabilityTaxPayableChartOfAccountId,
        ) {
            $items = [];

            if ($amountPayable != 0.0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $customerAccountReceivableChartOfAccountId,
                    sequence: count($items) + 1,
                    debit: $amountPayable,
                    credit: 0,
                    remarks: $salesInvoice->remarks,
                );
            }

            if ($netGoods != 0.0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $incomeSalesChartOfAccountId,
                    sequence: count($items) + 1,
                    debit: 0,
                    credit: $netGoods,
                    remarks: $salesInvoice->remarks,
                );
            }

            if ($vat != 0.0) {
                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $liabilityTaxPayableChartOfAccountId,
                    sequence: count($items) + 1,
                    debit: 0,
                    credit: $vat,
                    remarks: $salesInvoice->remarks,
                );
            }

            return $items;
        })();

        $this->saveJournalEntry(
            salesInvoice: $salesInvoice,
            existingJournalEntry: $salesInvoice->journalEntry,
            journalType: JournalEntryTypeEnum::TRANSACTION->value,
            date: $transactionDate,
            items: $transactionItems,
        );

        $closingPairs = [
            [
                'journalType' => JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
                'existing' => $salesInvoice->currentMonthEarningsJournalEntry,
                'date' => $transactionDate,
                'debitChartOfAccountId' => $systemSuspenseChartOfAccountId,
                'creditChartOfAccountId' => $equityCurrentMonthEarningsChartOfAccountId,
            ],
            [
                'journalType' => JournalEntryTypeEnum::MONTH_END_CLOSING->value,
                'existing' => $salesInvoice->monthEndClosingJournalEntry,
                'date' => $endOfMonthDate,
                'debitChartOfAccountId' => $incomeSalesChartOfAccountId,
                'creditChartOfAccountId' => $systemSuspenseChartOfAccountId,
            ],
            [
                'journalType' => JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
                'existing' => $salesInvoice->monthToYearClosingJournalEntry,
                'date' => $endOfMonthDate,
                'debitChartOfAccountId' => $equityCurrentMonthEarningsChartOfAccountId,
                'creditChartOfAccountId' => $equityCurrentYearEarningsChartOfAccountId,
            ],
            [
                'journalType' => JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
                'existing' => $salesInvoice->yearToRetainedEarningsClosingJournalEntry,
                'date' => $endOfYearDate,
                'debitChartOfAccountId' => $equityCurrentYearEarningsChartOfAccountId,
                'creditChartOfAccountId' => $equityRetainedEarningsChartOfAccountId,
            ],
        ];

        foreach ($closingPairs as $closingPair) {
            $items = [];

            if ($netGoods != 0.0) {
                $debitChartOfAccountId = $closingPair['debitChartOfAccountId'];
                $creditChartOfAccountId = $closingPair['creditChartOfAccountId'];

                if ($netGoods < 0) {
                    [$debitChartOfAccountId, $creditChartOfAccountId] = [$creditChartOfAccountId, $debitChartOfAccountId];
                }

                $absoluteNetGoods = abs($netGoods);

                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $debitChartOfAccountId,
                    sequence: count($items) + 1,
                    debit: $absoluteNetGoods,
                    credit: 0,
                    remarks: $salesInvoice->remarks,
                );

                $items[] = new JournalEntryItemDTO(
                    chartOfAccountId: $creditChartOfAccountId,
                    sequence: count($items) + 1,
                    debit: 0,
                    credit: $absoluteNetGoods,
                    remarks: $salesInvoice->remarks,
                );
            }

            $this->saveJournalEntry(
                salesInvoice: $salesInvoice,
                existingJournalEntry: $closingPair['existing'],
                journalType: $closingPair['journalType'],
                date: $closingPair['date'],
                items: $items,
            );
        }
    }

    private function saveJournalEntry(
        SalesInvoice $salesInvoice,
        ?JournalEntry $existingJournalEntry,
        string $journalType,
        mixed $date,
        array $items,
    ): void {
        if (count($items) == 0) {
            if ($existingJournalEntry) {
                $this->journalEntryActions->delete($existingJournalEntry);
            }

            return;
        }

        if (! $existingJournalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $salesInvoice->company_id,
                branchId: $salesInvoice->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $date,
                journalType: $journalType,
                sourceType: SalesInvoice::class,
                sourceId: $salesInvoice->id,
                referenceNo: $salesInvoice->code,
                remarks: $salesInvoice->remarks,
                items: $items,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $salesInvoice->branch_id,
                code: $existingJournalEntry->code,
                date: $date,
                journalType: $journalType,
                referenceNo: $salesInvoice->code,
                remarks: $salesInvoice->remarks,
                items: $items,
            );
            $this->journalEntryActions->update($existingJournalEntry, $journalEntryDTO);
        }
    }
}
