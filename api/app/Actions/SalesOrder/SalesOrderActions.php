<?php

namespace App\Actions\SalesOrder;

use App\Actions\SalesOrderItem\SalesOrderItemActions;
use App\Actions\SalesOrderPayment\SalesOrderPaymentActions;
use App\Actions\SalesOrderPaymentRefund\SalesOrderPaymentRefundActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\SalesOrderCreateDTO;
use App\DTOs\SalesOrderItemCreateDTO;
use App\DTOs\SalesOrderItemUpdateDTO;
use App\DTOs\SalesOrderPaymentCreateDTO;
use App\DTOs\SalesOrderPaymentRefundCreateDTO;
use App\DTOs\SalesOrderPaymentRefundUpdateDTO;
use App\DTOs\SalesOrderPaymentUpdateDTO;
use App\DTOs\SalesOrderUpdateDTO;
use App\Enums\ProgressStatusEnum;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.vatProfile',
        'payments.cashAccount',
        'refundedPayments.cashAccount',
    ];

    private $salesOrderItemActions;

    private $salesOrderPaymentActions;

    private $salesOrderPaymentRefundActions;

    public function __construct(
        SalesOrderItemActions $salesOrderItemActions,
        SalesOrderPaymentActions $salesOrderPaymentActions,
        SalesOrderPaymentRefundActions $salesOrderPaymentRefundActions,
    ) {
        $this->salesOrderItemActions = $salesOrderItemActions;
        $this->salesOrderPaymentActions = $salesOrderPaymentActions;
        $this->salesOrderPaymentRefundActions = $salesOrderPaymentRefundActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $customerId,
        ?string $progressStatus,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrder::select('sales_orders.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query->join('companies', 'companies.id', '=', 'sales_orders.company_id')
            ->whereCompanyId('sales_orders', $companyId)
            ->whereBranchId('sales_orders', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $customerId,
            $progressStatus,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_orders.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_orders.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('sales_orders.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_orders.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($customerId) {
                $query->where('sales_orders.customer_id', $customerId);
            }

            if ($progressStatus) {
                $query->where('sales_orders.progress_status', $progressStatus);
            }
        });

        $query->orderBy('sales_orders.date', 'desc')
            ->orderBy('sales_orders.id', 'asc');

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
                    $progressStatus ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_'.implode('_', $cacheParams);

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

    public function getProgressStatuses(): array
    {
        return ProgressStatusEnum::toDropDownOptions('views.sales_order.filters.progress_status_');
    }

    public function read(SalesOrder $salesOrder): SalesOrder
    {
        return $salesOrder->load(self::DETAIL_EAGER_LOADS);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->salesOrders()->withTrashed()->count() + 1 + $tryCount;
            $code = 'SO'.str_pad($count, 5, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesOrder::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    private function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function create(SalesOrderCreateDTO $data): SalesOrder
    {
        $timer_start = microtime(true);

        try {
            $salesOrder = new SalesOrder();
            $salesOrder->company_id = $data->companyId;
            $salesOrder->branch_id = $data->branchId;
            $salesOrder->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesOrder->date = $this->generateDate($data->date);
            $salesOrder->due_days = $data->dueDays;
            $salesOrder->customer_id = $data->customerId;
            $salesOrder->remarks = $data->remarks;
            $salesOrder->global_discount = $data->globalDiscount;
            $salesOrder->rounding = $data->rounding;
            $salesOrder->save();

            foreach ($data->items as $item) {
                $dto = new SalesOrderItemCreateDTO(
                    companyId: $salesOrder->company_id,
                    branchId: $salesOrder->branch_id,
                    salesOrderId: $salesOrder->id,
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                    priceDiscount: (float) $item['price_discount'],
                    subtotalDiscount: (float) $item['subtotal_discount'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'],
                );

                $this->salesOrderItemActions->create($dto, false);
            }

            foreach ($data->payments as $payment) {
                $dto = new SalesOrderPaymentCreateDTO(
                    companyId: $salesOrder->company_id,
                    branchId: $salesOrder->branch_id,
                    salesOrderId: $salesOrder->id,
                    code: $payment['code'],
                    date: $payment['date'],
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );

                $this->salesOrderPaymentActions->create($dto, false);
            }

            foreach ($data->refundedPayments as $refundedPayment) {
                $dto = new SalesOrderPaymentRefundCreateDTO(
                    companyId: $salesOrder->company_id,
                    branchId: $salesOrder->branch_id,
                    salesOrderId: $salesOrder->id,
                    code: $refundedPayment['code'],
                    date: $refundedPayment['date'],
                    cashAccountId: $refundedPayment['cash_account_id'],
                    amount: $refundedPayment['amount'],
                    remarks: $refundedPayment['remarks'],
                );

                $this->salesOrderPaymentRefundActions->create($dto, false);
            }

            self::updateSummary($salesOrder);

            $this->flushCache();

            return $salesOrder;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesOrder $salesOrder, SalesOrderUpdateDTO $data): SalesOrder
    {
        $timer_start = microtime(true);

        try {
            $salesOrder->code = $this->generateUniqueCode($salesOrder->company_id, $data->code, $salesOrder->id);
            $salesOrder->date = $this->generateDate($data->date);
            $salesOrder->due_days = $data->dueDays;
            $salesOrder->customer_id = $data->customerId;
            $salesOrder->remarks = $data->remarks;
            $salesOrder->global_discount = $data->globalDiscount;
            $salesOrder->rounding = $data->rounding;
            $salesOrder->save();

            foreach ($data->deleteItemIds as $deleteId) {
                $salesOrderItem = $salesOrder->items()->findOrFail($deleteId);
                $this->salesOrderItemActions->delete($salesOrderItem);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $salesOrderItem = $salesOrder->items()->findOrFail($item['id']);
                    $dto = new SalesOrderItemUpdateDTO(
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        productUnitPrice: $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                        priceDiscount: (float) $item['price_discount'],
                        subtotalDiscount: (float) $item['subtotal_discount'],
                        vatProfileId: $item['vat_profile_id'],
                        vatRate: $item['vat_rate'],
                        vatBaseNumerator: $item['vat_base_numerator'],
                        vatBaseDenominator: $item['vat_base_denominator'],
                        remarks: $item['remarks'],
                    );

                    $this->salesOrderItemActions->update($salesOrderItem, $dto, false);
                } else {
                    $dto = new SalesOrderItemCreateDTO(
                        companyId: $salesOrder->company_id,
                        branchId: $salesOrder->branch_id,
                        salesOrderId: $salesOrder->id,
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        productUnitPrice: $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                        priceDiscount: (float) $item['price_discount'],
                        subtotalDiscount: (float) $item['subtotal_discount'],
                        vatProfileId: $item['vat_profile_id'],
                        vatRate: $item['vat_rate'],
                        vatBaseNumerator: $item['vat_base_numerator'],
                        vatBaseDenominator: $item['vat_base_denominator'],
                        remarks: $item['remarks'],
                    );

                    $this->salesOrderItemActions->create($dto, false);
                }
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $salesOrderPayment = $salesOrder->payments()->findOrFail($deleteId);
                $this->salesOrderPaymentActions->delete($salesOrderPayment);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $salesOrderPayment = $salesOrder->payments()->findOrFail($payment['id']);
                    $dto = new SalesOrderPaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );

                    $this->salesOrderPaymentActions->update($salesOrderPayment, $dto, false);
                } else {
                    $dto = new SalesOrderPaymentCreateDTO(
                        companyId: $salesOrder->company_id,
                        branchId: $salesOrder->branch_id,
                        salesOrderId: $salesOrder->id,
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );

                    $this->salesOrderPaymentActions->create($dto, false);
                }
            }

            foreach ($data->deleteRefundedPaymentIds as $deleteId) {
                $salesOrderPaymentRefund = $salesOrder->refundedPayments()->findOrFail($deleteId);
                $this->salesOrderPaymentRefundActions->delete($salesOrderPaymentRefund);
            }

            foreach ($data->refundedPayments as $refundedPayment) {
                if (! empty($refundedPayment['id'])) {
                    $salesOrderPaymentRefund = $salesOrder->refundedPayments()->findOrFail($refundedPayment['id']);
                    $dto = new SalesOrderPaymentRefundUpdateDTO(
                        code: $refundedPayment['code'],
                        date: $refundedPayment['date'],
                        cashAccountId: $refundedPayment['cash_account_id'],
                        amount: $refundedPayment['amount'],
                        remarks: $refundedPayment['remarks'],
                    );

                    $this->salesOrderPaymentRefundActions->update($salesOrderPaymentRefund, $dto, false);
                } else {
                    $dto = new SalesOrderPaymentRefundCreateDTO(
                        companyId: $salesOrder->company_id,
                        branchId: $salesOrder->branch_id,
                        salesOrderId: $salesOrder->id,
                        code: $refundedPayment['code'],
                        date: $refundedPayment['date'],
                        cashAccountId: $refundedPayment['cash_account_id'],
                        amount: $refundedPayment['amount'],
                        remarks: $refundedPayment['remarks'],
                    );

                    $this->salesOrderPaymentRefundActions->create($dto, false);
                }
            }

            self::updateSummary($salesOrder);

            $this->flushCache();

            return $salesOrder;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(SalesOrder $salesOrder): void
    {
        $so = $salesOrder;
        $so->refresh();

        $so->item_total_before_global_discount = $so->items->sum('subtotal_after_discount');
        $so->global_discount = (function () use ($so) {
            $beforeDiscount = (float) $so->item_total_before_global_discount;
            $globalDiscount = max((float) $so->global_discount, 0);

            return min($globalDiscount, $beforeDiscount);
        })();

        foreach ($so->items as $soItem) {
            $soItem->global_discount = (function () use ($soItem, $so) {
                $itemTotalBeforeGlobalDiscount = (float) $so->item_total_before_global_discount;
                $salesOrderGlobalDiscount = (float) $so->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $salesOrderGlobalDiscount <= 0) return 0;

                $value = ((float) $soItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $salesOrderGlobalDiscount;

                return $value < 0 ? 0 : $value;
            })();
            $soItem->subtotal_after_global_discount = $soItem->subtotal_after_discount - $soItem->global_discount;
            $soItem->save();
        }

        $globalDiscountDifference = round((float) $so->global_discount - (float) $so->items->sum('global_discount'), 8);
        if (abs($globalDiscountDifference) > 0.00000001) {
            $lastGlobalDiscountSoItem = $so->items
                ->filter(fn ($soItem) => (float) $soItem->subtotal_after_discount > 0)
                ->last();

            if ($lastGlobalDiscountSoItem) {
                $lastGlobalDiscountSoItem->global_discount = max(
                    0,
                    (float) $lastGlobalDiscountSoItem->global_discount + $globalDiscountDifference
                );
                $lastGlobalDiscountSoItem->subtotal_after_global_discount = max(
                    0,
                    (float) $lastGlobalDiscountSoItem->subtotal_after_discount - (float) $lastGlobalDiscountSoItem->global_discount
                );
                $lastGlobalDiscountSoItem->save();
            }
        }

        $so->item_total_after_global_discount = (float) $so->items->sum('subtotal_after_global_discount');

        foreach ($so->items as $soItem) {
            $soItem->vat_base = (function () use ($soItem) {
                $subtotalAfterGlobalDiscount = (float) $soItem->subtotal_after_global_discount;
                $vatRate = (float) $soItem->vat_rate;
                $vatBaseFactor = $soItem->vat_base_denominator > 0
                    ? (float) $soItem->vat_base_numerator / (float) $soItem->vat_base_denominator
                    : 0;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) return 0;

                if ($soItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();
            $soItem->vat = (function () use ($soItem) {
                $vatBase = (float) $soItem->vat_base;
                $vatRate = (float) $soItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) return 0;

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();
            $soItem->subtotal_after_vat = (function () use ($soItem) {
                $subtotalAfterGlobalDiscount = (float) $soItem->subtotal_after_global_discount;
                $vat = (float) $soItem->vat;

                if ($soItem->product_unit_is_price_include_vat) {
                    return $subtotalAfterGlobalDiscount;
                }

                return $subtotalAfterGlobalDiscount + $vat;
            })();
            $soItem->save();
        }

        $so->vat_base = (float) $so->items->sum('vat_base');
        $so->vat = (float) $so->items->sum('vat');
        $so->item_total_after_vat = (float) $so->items->sum('subtotal_after_vat');

        foreach ($so->items as $soItem) {
            $soItem->rounding = (function () use ($soItem, $so) {
                $soItemSubtotalAfterVat = (float) $soItem->subtotal_after_vat;
                $itemTotalAfterVat = (float) $so->item_total_after_vat;
                $salesOrderRounding = (float) $so->rounding;

                if ($itemTotalAfterVat <= 0 || $salesOrderRounding == 0 || $soItemSubtotalAfterVat <= 0) return 0;

                return ($soItemSubtotalAfterVat / $itemTotalAfterVat) * $salesOrderRounding;
            })();
            $soItem->amount_payable = (float) $soItem->subtotal_after_vat + (float) $soItem->rounding;
            $soItem->save();
        }

        $roundingDifference = round((float) $so->rounding - (float) $so->items->sum('rounding'), 8);
        if (abs($roundingDifference) > 0.00000001) {
            $lastRoundingSoItem = $so->items
                ->filter(fn ($soItem) => (float) $soItem->subtotal_after_vat > 0)
                ->last();

            if ($lastRoundingSoItem) {
                $lastRoundingSoItem->rounding = (float) $lastRoundingSoItem->rounding + $roundingDifference;
                $lastRoundingSoItem->amount_payable = (float) $lastRoundingSoItem->subtotal_after_vat
                    + (float) $lastRoundingSoItem->rounding;
                $lastRoundingSoItem->save();
            }
        }

        $so->amount_payable = (float) $so->items->sum('amount_payable');
        $so->amount_paid_down_payment = $so->payments->sum('amount');
        $so->amount_allocated_down_payment = $so->payments->sum('amount_allocated');
        $so->amount_refunded_down_payment = $so->refundedPayments->sum('amount');
        $so->amount_available_down_payment = $so->amount_paid_down_payment - $so->amount_allocated_down_payment - $so->amount_refunded_down_payment;

        foreach ($so->items as $soItem) {
            $qtyTargetBase = (float) $soItem->product_unit_qty_base;
            $soItem->qty_delivered_base = (float) $so->deliveryItems()
                ->where('product_id', $soItem->product_id)
                ->sum('product_unit_qty_base');
            $soItem->qty_invoiced_base = (float) $so->invoiceItems()
                ->where('product_id', $soItem->product_id)
                ->sum('product_unit_qty_base');
            $soItem->qty_outstanding_base = max($qtyTargetBase - $soItem->qty_delivered_base, 0);
            $soItem->qty_excess_base = max($soItem->qty_delivered_base - $qtyTargetBase, 0);
            $soItem->save();
        }

        $so->item_total_count = (function () use ($so) {
            return $so->items()->count();
        })();
        $so->item_matched_count = (function () use ($so) {
            $itemMatchedCount = 0;

            foreach ($so->items as $soItem) {
                if (! $so->deliveryItems()->where('product_id', $soItem->product_id)->exists()) {
                    continue;
                }

                if ((float) $soItem->qty_excess_base > 0) {
                    continue;
                }

                if ((float) $soItem->qty_outstanding_base > 0) {
                    continue;
                }

                $itemMatchedCount++;
            }

            return $itemMatchedCount;
        })();
        $so->item_less_count = (function () use ($so) {
            $itemLessCount = 0;

            foreach ($so->items as $soItem) {
                if (! $so->deliveryItems()->where('product_id', $soItem->product_id)->exists()) {
                    continue;
                }

                if ((float) $soItem->qty_excess_base > 0) {
                    continue;
                }

                if ((float) $soItem->qty_outstanding_base > 0) {
                    $itemLessCount++;
                }
            }

            return $itemLessCount;
        })();
        $so->item_more_count = (function () use ($so) {
            $itemMoreCount = 0;

            foreach ($so->items as $soItem) {
                if (! $so->deliveryItems()->where('product_id', $soItem->product_id)->exists()) {
                    continue;
                }

                if ((float) $soItem->qty_excess_base > 0) {
                    $itemMoreCount++;
                }
            }

            return $itemMoreCount;
        })();
        $so->item_unlinked_count = (function () use ($so) {
            $itemUnlinkedCount = 0;

            foreach ($so->items as $soItem) {
                if (! $so->deliveryItems()->where('product_id', $soItem->product_id)->exists()) {
                    $itemUnlinkedCount++;
                }
            }

            return $itemUnlinkedCount;
        })();
        $so->progress_status = (function () use ($so) {
            if ($so->item_total_count === 0) {
                return ProgressStatusEnum::UNLINKED;
            }

            if ($so->item_unlinked_count === $so->item_total_count) {
                return ProgressStatusEnum::UNLINKED;
            }

            if ($so->item_matched_count === $so->item_total_count) {
                return ProgressStatusEnum::MATCHED;
            }

            return ProgressStatusEnum::UNMATCHED;
        })();

        $salesOrderProductIds = $so->items()->distinct()->pluck('product_id')->filter()->values()->all();
        $so->deliveryItems()->whereIn('product_id', $salesOrderProductIds)->update(['has_sales_order_item_product' => true]);
        $so->deliveryItems()->whereNotIn('product_id', $salesOrderProductIds)->update(['has_sales_order_item_product' => false]);

        $so->save();
    }

    public function delete(SalesOrder $salesOrder): bool
    {
        $timer_start = microtime(true);

        try {
            if ($salesOrder->deliveries()->exists()
                || $salesOrder->invoices()->exists()
                || $salesOrder->payments()->where('amount_allocated', '>', 0)->exists()) {
                throw new Exception('Sales order cannot be deleted because it already has related transactions.');
            }

            foreach ($salesOrder->items as $salesOrderItem) {
                $this->salesOrderItemActions->delete($salesOrderItem);
            }

            foreach ($salesOrder->payments as $salesOrderPayment) {
                $this->salesOrderPaymentActions->delete($salesOrderPayment);
            }

            foreach ($salesOrder->refundedPayments as $salesOrderPaymentRefund) {
                $this->salesOrderPaymentRefundActions->delete($salesOrderPaymentRefund);
            }

            $result = $salesOrder->delete();

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
}
