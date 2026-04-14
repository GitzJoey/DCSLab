<?php

namespace App\Actions\PurchaseOrderGlobalDiscount;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderGlobalDiscountCreateDTO;
use App\DTOs\PurchaseOrderGlobalDiscountUpdateDTO;
use App\Enums\DiscountTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderGlobalDiscount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderGlobalDiscountActions
{
    use CacheHelper;
    use LoggerHelper;

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $purchaseOrderCode,
        ?string $purchaseOrderStartDate,
        ?string $purchaseOrderEndDate,
        ?int $purchaseOrderSupplierId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderGlobalDiscount::select('purchase_order_global_discounts.*')
            ->with([
                'company',
                'branch',
                'purchaseOrder.supplier',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_order_global_discounts.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_global_discounts.purchase_order_id')
            ->whereCompanyId('purchase_order_global_discounts', $companyId)
            ->whereBranchId('purchase_order_global_discounts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseOrderCode,
            $purchaseOrderStartDate,
            $purchaseOrderEndDate,
            $purchaseOrderSupplierId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            $purchaseOrderStartDateUtc = $purchaseOrderStartDate ? TimezoneHelper::convertToUTC($purchaseOrderStartDate) : null;
            if ($purchaseOrderStartDateUtc) {
                $query->where('purchase_orders.date', '>=', $purchaseOrderStartDateUtc);
            }

            $purchaseOrderEndDateUtc = $purchaseOrderEndDate ? TimezoneHelper::convertToUTC($purchaseOrderEndDate) : null;
            if ($purchaseOrderEndDateUtc) {
                $query->where('purchase_orders.date', '<=', $purchaseOrderEndDateUtc);
            }

            if ($purchaseOrderCode) {
                $query->where('purchase_orders.code', $purchaseOrderCode);
            }

            if ($purchaseOrderSupplierId) {
                $query->where('purchase_orders.supplier_id', $purchaseOrderSupplierId);
            }
        });

        $query->orderBy('purchase_orders.date', 'desc')
            ->orderBy('purchase_order_global_discounts.sequence', 'asc')
            ->orderBy('purchase_order_global_discounts.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($purchaseOrderCode) ? '[empty]' : $purchaseOrderCode,
                    $purchaseOrderStartDate ?? '[null]',
                    $purchaseOrderEndDate ?? '[null]',
                    $purchaseOrderSupplierId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_global_discount_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderGlobalDiscount $purchaseOrderGlobalDiscount): PurchaseOrderGlobalDiscount
    {
        return $purchaseOrderGlobalDiscount->load([
            'company',
            'branch',
            'purchaseOrder.supplier',
        ]);
    }

    public function getAmountByPurchaseOrderId(int $purchaseOrderId): float
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with(['globalDiscounts' => fn ($query) => $query->orderBy('sequence')->orderBy('id')])
            ->findOrFail($purchaseOrderId);

        $beforeDiscount = (float) $purchaseOrder->item_total_before_global_discount;
        $afterDiscount = $beforeDiscount;

        foreach ($purchaseOrder->globalDiscounts as $globalDiscount) {
            $discountType = $globalDiscount->discount_type instanceof DiscountTypeEnum
                ? $globalDiscount->discount_type
                : DiscountTypeEnum::resolveToEnum($globalDiscount->discount_type);
            $discountValue = (float) $globalDiscount->discount_value;

            if ($discountType === DiscountTypeEnum::PERCENTAGE) {
                $afterDiscount -= $afterDiscount * $discountValue / 100;
            } else {
                $afterDiscount -= $discountValue;
            }

            if ($afterDiscount < 0) {
                $afterDiscount = 0;
            }
        }

        return $beforeDiscount - $afterDiscount;
    }

    public function create(PurchaseOrderGlobalDiscountCreateDTO $data): PurchaseOrderGlobalDiscount
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderGlobalDiscount = new PurchaseOrderGlobalDiscount();
            $purchaseOrderGlobalDiscount->company_id = $data->companyId;
            $purchaseOrderGlobalDiscount->branch_id = $data->branchId;
            $purchaseOrderGlobalDiscount->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderGlobalDiscount->sequence = $data->sequence;
            $purchaseOrderGlobalDiscount->discount_type = $data->discountType;
            $purchaseOrderGlobalDiscount->discount_value = $data->discountValue;
            $purchaseOrderGlobalDiscount->save();

            $this->flushCache();

            return $purchaseOrderGlobalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderGlobalDiscount $purchaseOrderGlobalDiscount, PurchaseOrderGlobalDiscountUpdateDTO $data): PurchaseOrderGlobalDiscount
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderGlobalDiscount->sequence = $data->sequence;
            $purchaseOrderGlobalDiscount->discount_type = $data->discountType;
            $purchaseOrderGlobalDiscount->discount_value = $data->discountValue;
            $purchaseOrderGlobalDiscount->save();

            $this->flushCache();

            return $purchaseOrderGlobalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderGlobalDiscount $purchaseOrderGlobalDiscount): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $purchaseOrderGlobalDiscount->delete();

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
