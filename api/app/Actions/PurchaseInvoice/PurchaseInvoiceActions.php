<?php

namespace App\Actions\PurchaseInvoice;

use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseInvoiceItem\PurchaseInvoiceItemActions;
use App\Actions\PurchaseInvoicePayment\PurchaseInvoicePaymentActions;
use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseInvoiceCreateDTO;
use App\DTOs\PurchaseInvoiceItemCreateDTO;
use App\DTOs\PurchaseInvoiceItemUpdateDTO;
use App\DTOs\PurchaseInvoicePaymentCreateDTO;
use App\DTOs\PurchaseInvoicePaymentUpdateDTO;
use App\DTOs\PurchaseInvoiceUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\PurchaseInvoice;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseInvoiceActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseOrder',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseOrder.items',
        'items.productUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.baseProductUnit.unit',
        'items.vatProfile',
        'items.purchaseOrderItem',
        'payments.cashAccount',
        'payments.purchaseOrderPayment',
        'payments.purchaseReturn',
    ];

    public function __construct(
        private readonly PurchaseInvoiceItemActions $purchaseInvoiceItemActions,
        private readonly PurchaseInvoicePaymentActions $purchaseInvoicePaymentActions,
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
        ?int $purchaseOrderId,
        ?bool $isPosted,
        ?bool $isPaidOff,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseInvoice::select('purchase_invoices.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('purchase_invoices', $companyId)
            ->whereBranchId('purchase_invoices', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $supplierId,
            $purchaseOrderId,
            $isPosted,
            $isPaidOff,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_invoices.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_invoices.tax_invoice_number', 'like', '%'.$search.'%')
                        ->orWhere('purchase_invoices.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_invoices.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_invoices.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if (! is_null($supplierId)) {
                $query->where('purchase_invoices.supplier_id', $supplierId);
            }

            if (! is_null($purchaseOrderId)) {
                $query->where('purchase_invoices.purchase_order_id', $purchaseOrderId);
            }

            if (! is_null($isPosted)) {
                $query->where('purchase_invoices.is_posted', $isPosted);
            }

            if (! is_null($isPaidOff)) {
                $query->where('purchase_invoices.is_paid_off', $isPaidOff);
            }
        });

        $query->orderBy('purchase_invoices.date', 'desc');
        $query->orderBy('purchase_invoices.id', 'asc');

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
                    $purchaseOrderId ?? '[null]',
                    is_null($isPosted) ? '[null]' : ($isPosted ? 'true' : 'false'),
                    is_null($isPaidOff) ? '[null]' : ($isPaidOff ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_invoice_'.implode('_', $cacheParams);

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

    public function read(PurchaseInvoice $purchaseInvoice): PurchaseInvoice
    {
        return $purchaseInvoice->load(self::DETAIL_EAGER_LOADS);
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
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->purchaseInvoices()->withTrashed()->count() + 1 + $tryCount;
                $code = 'PI'.str_pad($count, 5, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseInvoice::whereCompanyId('purchase_invoices', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseInvoiceCreateDTO $data): PurchaseInvoice
    {
        $timer_start = microtime(true);

        try {
            $purchaseInvoice = new PurchaseInvoice();
            $purchaseInvoice->company_id = $data->companyId;
            $purchaseInvoice->branch_id = $data->branchId;
            $purchaseInvoice->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseInvoice->date = $this->generateDate($data->date);
            $purchaseInvoice->due_days = $data->dueDays;
            $purchaseInvoice->supplier_id = $data->supplierId;
            $purchaseInvoice->purchase_order_id = $data->purchaseOrderId;
            $purchaseInvoice->tax_invoice_number = $data->taxInvoiceNumber;
            $purchaseInvoice->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $purchaseInvoice->tax_invoice_vat = $data->taxInvoiceVat;
            $purchaseInvoice->remarks = $data->remarks;
            $purchaseInvoice->is_posted = $data->isPosted;
            $purchaseInvoice->global_discount = $data->globalDiscount;
            $purchaseInvoice->rounding = $data->rounding;
            $purchaseInvoice->save();

            foreach ($data->items as $item) {
                $this->purchaseInvoiceItemActions->create(
                    data: new PurchaseInvoiceItemCreateDTO(
                        companyId: $purchaseInvoice->company_id,
                        branchId: $purchaseInvoice->branch_id,
                        purchaseInvoiceId: $purchaseInvoice->id,
                        purchaseOrderItemId: $item['purchase_order_item_id'] ?? null,
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
                    ),
                    updateParentSummary: false,
                );
            }

            foreach ($data->payments as $payment) {
                $this->purchaseInvoicePaymentActions->create(
                    data: new PurchaseInvoicePaymentCreateDTO(
                        companyId: $purchaseInvoice->company_id,
                        branchId: $purchaseInvoice->branch_id,
                        purchaseInvoiceId: $purchaseInvoice->id,
                        code: $payment['code'],
                        date: $payment['date'],
                        paymentType: $payment['payment_type'],
                        cashAccountId: $payment['cash_account_id'] ?? null,
                        purchaseOrderPaymentId: $payment['purchase_order_payment_id'] ?? null,
                        purchaseReturnId: $payment['purchase_return_id'] ?? null,
                        amount: (float) $payment['amount'],
                        remarks: $payment['remarks'] ?? null,
                    ),
                    updateParentSummary: false,
                );
            }

            self::updateSummary($purchaseInvoice);

            $this->syncTransactionJournal($purchaseInvoice);

            PurchaseOrderActions::updateSummary($purchaseInvoice->purchaseOrder);

            $this->flushCache();

            return $purchaseInvoice;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseInvoice $purchaseInvoice, PurchaseInvoiceUpdateDTO $data): PurchaseInvoice
    {
        $timer_start = microtime(true);

        try {
            $previousPurchaseOrder = $purchaseInvoice->purchaseOrder;

            $purchaseInvoice->code = $this->generateUniqueCode($purchaseInvoice->company_id, $data->code, $purchaseInvoice->id);
            $purchaseInvoice->date = $this->generateDate($data->date);
            $purchaseInvoice->due_days = $data->dueDays;
            $purchaseInvoice->supplier_id = $data->supplierId;
            $purchaseInvoice->purchase_order_id = $data->purchaseOrderId;
            $purchaseInvoice->tax_invoice_number = $data->taxInvoiceNumber;
            $purchaseInvoice->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $purchaseInvoice->tax_invoice_vat = $data->taxInvoiceVat;
            $purchaseInvoice->remarks = $data->remarks;
            $purchaseInvoice->is_posted = $data->isPosted;
            $purchaseInvoice->global_discount = $data->globalDiscount;
            $purchaseInvoice->rounding = $data->rounding;
            $purchaseInvoice->save();

            foreach ($data->deleteItemIds as $deleteItemId) {
                $purchaseInvoiceItem = $purchaseInvoice->items()->findOrFail($deleteItemId);
                $this->purchaseInvoiceItemActions->delete($purchaseInvoiceItem, updateParentSummary: false);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $purchaseInvoiceItem = $purchaseInvoice->items()->findOrFail($item['id']);
                    $this->purchaseInvoiceItemActions->update(
                        purchaseInvoiceItem: $purchaseInvoiceItem,
                        data: new PurchaseInvoiceItemUpdateDTO(
                            purchaseOrderItemId: $item['purchase_order_item_id'] ?? null,
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
                        ),
                        updateParentSummary: false,
                    );
                } else {
                    $this->purchaseInvoiceItemActions->create(
                        data: new PurchaseInvoiceItemCreateDTO(
                            companyId: $purchaseInvoice->company_id,
                            branchId: $purchaseInvoice->branch_id,
                            purchaseInvoiceId: $purchaseInvoice->id,
                            purchaseOrderItemId: $item['purchase_order_item_id'] ?? null,
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
                        ),
                        updateParentSummary: false,
                    );
                }
            }

            foreach ($data->deletePaymentIds as $deletePaymentId) {
                $purchaseInvoicePayment = $purchaseInvoice->payments()->findOrFail($deletePaymentId);
                $this->purchaseInvoicePaymentActions->delete($purchaseInvoicePayment, updateParentSummary: false);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $purchaseInvoicePayment = $purchaseInvoice->payments()->findOrFail($payment['id']);
                    $this->purchaseInvoicePaymentActions->update(
                        purchaseInvoicePayment: $purchaseInvoicePayment,
                        data: new PurchaseInvoicePaymentUpdateDTO(
                            code: $payment['code'],
                            date: $payment['date'],
                            paymentType: $payment['payment_type'],
                            cashAccountId: $payment['cash_account_id'] ?? null,
                            purchaseOrderPaymentId: $payment['purchase_order_payment_id'] ?? null,
                            purchaseReturnId: $payment['purchase_return_id'] ?? null,
                            amount: (float) $payment['amount'],
                            remarks: $payment['remarks'] ?? null,
                        ),
                        updateParentSummary: false,
                    );
                } else {
                    $this->purchaseInvoicePaymentActions->create(
                        data: new PurchaseInvoicePaymentCreateDTO(
                            companyId: $purchaseInvoice->company_id,
                            branchId: $purchaseInvoice->branch_id,
                            purchaseInvoiceId: $purchaseInvoice->id,
                            code: $payment['code'],
                            date: $payment['date'],
                            paymentType: $payment['payment_type'],
                            cashAccountId: $payment['cash_account_id'] ?? null,
                            purchaseOrderPaymentId: $payment['purchase_order_payment_id'] ?? null,
                            purchaseReturnId: $payment['purchase_return_id'] ?? null,
                            amount: (float) $payment['amount'],
                            remarks: $payment['remarks'] ?? null,
                        ),
                        updateParentSummary: false,
                    );
                }
            }

            self::updateSummary($purchaseInvoice);

            $this->syncTransactionJournal($purchaseInvoice);

            if ($previousPurchaseOrder) {
                PurchaseOrderActions::updateSummary($previousPurchaseOrder->refresh());
            }

            if ($purchaseInvoice->purchaseOrder) {
                if (! $previousPurchaseOrder || $purchaseInvoice->purchaseOrder->id !== $previousPurchaseOrder->id) {
                    PurchaseOrderActions::updateSummary($purchaseInvoice->purchaseOrder);
                }
            }

            $this->flushCache();

            return $purchaseInvoice;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseInvoice $purchaseInvoice): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            if (
                $purchaseInvoice->payments()->exists()
                || $purchaseInvoice->returns()->exists()
            ) {
                throw new Exception('Purchase invoice cannot be deleted because it already has related transactions.');
            }

            $purchaseOrder = $purchaseInvoice->purchaseOrder;

            foreach ($purchaseInvoice->items as $purchaseInvoiceItem) {
                $this->purchaseInvoiceItemActions->delete($purchaseInvoiceItem, updateParentSummary: false);
            }

            $journalEntry = $purchaseInvoice->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $retval = $purchaseInvoice->delete();

            if ($purchaseOrder) {
                PurchaseOrderActions::updateSummary($purchaseOrder->refresh());
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
     * Recompute the totals waterfall (single nominal global discount pro-rated
     * with residue into the last item, VAT chain, rounding pro-rate, net-of-VAT
     * cogs) and the payment roll-ups of the invoice.
     */
    public static function updateSummary(PurchaseInvoice $purchaseInvoice): void
    {
        $purchaseInvoice->refresh();

        $purchaseInvoice->item_total_before_global_discount = (float) $purchaseInvoice->items()->sum('subtotal_after_discount');
        $purchaseInvoice->global_discount = min(
            max((float) $purchaseInvoice->global_discount, 0),
            (float) $purchaseInvoice->item_total_before_global_discount
        );

        foreach ($purchaseInvoice->items as $purchaseInvoiceItem) {
            $purchaseInvoiceItem->global_discount = (function () use ($purchaseInvoiceItem, $purchaseInvoice) {
                $itemTotalBeforeGlobalDiscount = (float) $purchaseInvoice->item_total_before_global_discount;
                $globalDiscount = (float) $purchaseInvoice->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $globalDiscount <= 0) return 0;

                $value = ((float) $purchaseInvoiceItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $globalDiscount;

                return $value < 0 ? 0 : $value;
            })();
            $purchaseInvoiceItem->subtotal_after_global_discount = max(
                0,
                (float) $purchaseInvoiceItem->subtotal_after_discount - (float) $purchaseInvoiceItem->global_discount
            );
            $purchaseInvoiceItem->save();
        }

        $globalDiscountDifference = round((float) $purchaseInvoice->global_discount - (float) $purchaseInvoice->items()->sum('global_discount'), 8);
        if (abs($globalDiscountDifference) > 0.00000001) {
            $lastGlobalDiscountItem = $purchaseInvoice->items
                ->filter(fn ($purchaseInvoiceItem) => (float) $purchaseInvoiceItem->subtotal_after_discount > 0)
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

        $purchaseInvoice->item_total_after_global_discount = (float) $purchaseInvoice->items()->sum('subtotal_after_global_discount');

        foreach ($purchaseInvoice->items as $purchaseInvoiceItem) {
            $purchaseInvoiceItem->vat_base = (function () use ($purchaseInvoiceItem) {
                $subtotalAfterGlobalDiscount = (float) $purchaseInvoiceItem->subtotal_after_global_discount;
                $vatRate = (float) $purchaseInvoiceItem->vat_rate;
                $vatBaseFactor = $purchaseInvoiceItem->vat_base_denominator > 0
                    ? (float) $purchaseInvoiceItem->vat_base_numerator / (float) $purchaseInvoiceItem->vat_base_denominator
                    : 0;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) return 0;

                if ($purchaseInvoiceItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();
            $purchaseInvoiceItem->vat = (function () use ($purchaseInvoiceItem) {
                $vatBase = (float) $purchaseInvoiceItem->vat_base;
                $vatRate = (float) $purchaseInvoiceItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) return 0;

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();
            $purchaseInvoiceItem->subtotal_after_vat = (function () use ($purchaseInvoiceItem) {
                $subtotalAfterGlobalDiscount = (float) $purchaseInvoiceItem->subtotal_after_global_discount;
                $vat = (float) $purchaseInvoiceItem->vat;

                if ($purchaseInvoiceItem->product_unit_is_price_include_vat) {
                    return $subtotalAfterGlobalDiscount;
                }

                return $subtotalAfterGlobalDiscount + $vat;
            })();
            $purchaseInvoiceItem->save();
        }

        $purchaseInvoice->vat_base = (float) $purchaseInvoice->items()->sum('vat_base');
        $purchaseInvoice->vat = (float) $purchaseInvoice->items()->sum('vat');
        $purchaseInvoice->item_total_after_vat = (float) $purchaseInvoice->items()->sum('subtotal_after_vat');

        foreach ($purchaseInvoice->items as $purchaseInvoiceItem) {
            $purchaseInvoiceItem->rounding = (function () use ($purchaseInvoiceItem, $purchaseInvoice) {
                $itemSubtotalAfterVat = (float) $purchaseInvoiceItem->subtotal_after_vat;
                $itemTotalAfterVat = (float) $purchaseInvoice->item_total_after_vat;
                $invoiceRounding = (float) $purchaseInvoice->rounding;

                if ($itemTotalAfterVat <= 0 || $invoiceRounding == 0 || $itemSubtotalAfterVat <= 0) return 0;

                return ($itemSubtotalAfterVat / $itemTotalAfterVat) * $invoiceRounding;
            })();
            $purchaseInvoiceItem->save();
        }

        $roundingDifference = round((float) $purchaseInvoice->rounding - (float) $purchaseInvoice->items()->sum('rounding'), 8);
        if (abs($roundingDifference) > 0.00000001) {
            $lastRoundingItem = $purchaseInvoice->items
                ->filter(fn ($purchaseInvoiceItem) => (float) $purchaseInvoiceItem->subtotal_after_vat > 0)
                ->last();

            if ($lastRoundingItem) {
                $lastRoundingItem->rounding = (float) $lastRoundingItem->rounding + $roundingDifference;
                $lastRoundingItem->save();
            }
        }

        foreach ($purchaseInvoice->items as $purchaseInvoiceItem) {
            $purchaseInvoiceItem->amount_payable = (float) $purchaseInvoiceItem->subtotal_after_vat
                + (float) $purchaseInvoiceItem->rounding;
            $purchaseInvoiceItem->cogs = (function () use ($purchaseInvoiceItem) {
                $qty = (float) $purchaseInvoiceItem->qty;
                $netAmount = (float) $purchaseInvoiceItem->amount_payable - (float) $purchaseInvoiceItem->vat;

                if ($qty <= 0 || $netAmount <= 0) return 0;

                return $netAmount / $qty;
            })();
            $purchaseInvoiceItem->total_cogs = (function () use ($purchaseInvoiceItem) {
                $qty = (float) $purchaseInvoiceItem->qty;
                $cogs = (float) $purchaseInvoiceItem->cogs;

                if ($qty <= 0 || $cogs <= 0) return 0;

                return $qty * $cogs;
            })();
            $purchaseInvoiceItem->base_unit_cogs = (function () use ($purchaseInvoiceItem) {
                $productUnitQtyBase = (float) $purchaseInvoiceItem->product_unit_qty_base;
                $totalCogs = (float) $purchaseInvoiceItem->total_cogs;

                if ($productUnitQtyBase <= 0 || $totalCogs <= 0) return 0;

                return $totalCogs / $productUnitQtyBase;
            })();
            $purchaseInvoiceItem->save();
        }

        $purchaseInvoice->amount_payable = (float) $purchaseInvoice->items()->sum('amount_payable');
        $purchaseInvoice->amount_paid_down_payment = (float) $purchaseInvoice->payments()
            ->where('payment_type', PaymentTypeEnum::DOWN_PAYMENT->value)
            ->sum('amount');
        $purchaseInvoice->amount_paid_return = (float) $purchaseInvoice->payments()
            ->where('payment_type', PaymentTypeEnum::RETURN->value)
            ->sum('amount');
        $purchaseInvoice->amount_paid_total = (float) $purchaseInvoice->payments()->sum('amount');
        $purchaseInvoice->amount_due = max(0, (float) $purchaseInvoice->amount_payable - (float) $purchaseInvoice->amount_paid_total);
        $purchaseInvoice->is_paid_off = $purchaseInvoice->amount_due == 0;

        $purchaseInvoice->save();
    }

    /**
     * Create / update / delete the TRANSACTION journal entry of the invoice (P5):
     * Dr GRNI (net goods) + Dr VAT-in (vat, omitted when 0) / Cr supplier AP
     * (amount payable, fallback company account payable).
     */
    private function syncTransactionJournal(PurchaseInvoice $purchaseInvoice): void
    {
        $journalEntry = $purchaseInvoice->journalEntry;
        $journalItems = $this->buildTransactionJournalItems($purchaseInvoice);

        if (count($journalItems) === 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        if (! $journalEntry) {
            $this->journalEntryActions->create(new JournalEntryCreateDTO(
                companyId: $purchaseInvoice->company_id,
                branchId: $purchaseInvoice->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $purchaseInvoice->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: PurchaseInvoice::class,
                sourceId: $purchaseInvoice->id,
                referenceNo: $purchaseInvoice->code,
                remarks: $purchaseInvoice->remarks,
                items: $journalItems,
            ));
        } else {
            $this->journalEntryActions->update($journalEntry, new JournalEntryUpdateDTO(
                branchId: $purchaseInvoice->branch_id,
                code: $journalEntry->code,
                date: $purchaseInvoice->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $purchaseInvoice->code,
                remarks: $purchaseInvoice->remarks,
                items: $journalItems,
            ));
        }
    }

    /**
     * @return JournalEntryItemDTO[]
     */
    private function buildTransactionJournalItems(PurchaseInvoice $purchaseInvoice): array
    {
        $amountPayable = (float) $purchaseInvoice->amount_payable;
        $vat = (float) $purchaseInvoice->vat;
        $netGoods = $amountPayable - $vat;

        $items = [];

        if ($netGoods > 0) {
            $items[] = new JournalEntryItemDTO(
                chartOfAccountId: $purchaseInvoice->company->liabilityGoodsReceivedNotInvoicedChartOfAccount?->id,
                sequence: count($items) + 1,
                debit: $netGoods,
                credit: 0,
                remarks: $purchaseInvoice->remarks,
            );
        }

        if ($vat > 0) {
            $items[] = new JournalEntryItemDTO(
                chartOfAccountId: $purchaseInvoice->company->assetCurrentVatInChartOfAccount?->id,
                sequence: count($items) + 1,
                debit: $vat,
                credit: 0,
                remarks: $purchaseInvoice->remarks,
            );
        }

        if ($amountPayable > 0) {
            $items[] = new JournalEntryItemDTO(
                chartOfAccountId: $purchaseInvoice->supplier?->chartOfAccount?->id
                    ?? $purchaseInvoice->company->liabilityAccountPayableChartOfAccount?->id,
                sequence: count($items) + 1,
                debit: 0,
                credit: $amountPayable,
                remarks: $purchaseInvoice->remarks,
            );
        }

        return $items;
    }
}
