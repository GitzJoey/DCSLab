<?php

namespace App\Actions\PurchaseGlobalDiscount;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseGlobalDiscountCreateDTO;
use App\DTOs\PurchaseGlobalDiscountUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseGlobalDiscount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseGlobalDiscountActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchase.supplier',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $purchaseCode,
        ?string $purchaseStartDate,
        ?string $purchaseEndDate,
        ?int $purchaseSupplierId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseGlobalDiscount::select('purchase_global_discounts.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_global_discounts.company_id')
            ->join('purchases', 'purchases.id', '=', 'purchase_global_discounts.purchase_id')
            ->whereCompanyId('purchase_global_discounts', $companyId)
            ->whereBranchId('purchase_global_discounts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseCode,
            $purchaseStartDate,
            $purchaseEndDate,
            $purchaseSupplierId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_global_discounts.discount_type', 'like', '%'.$search.'%');
                });
            }

            $purchaseStartDateUtc = $purchaseStartDate ? TimezoneHelper::convertToUTC($purchaseStartDate) : null;
            if ($purchaseStartDateUtc) {
                $query->where('purchases.date', '>=', $purchaseStartDateUtc);
            }

            $purchaseEndDateUtc = $purchaseEndDate ? TimezoneHelper::convertToUTC($purchaseEndDate) : null;
            if ($purchaseEndDateUtc) {
                $query->where('purchases.date', '<=', $purchaseEndDateUtc);
            }

            if ($purchaseCode) {
                $query->where('purchases.code', $purchaseCode);
            }

            if ($purchaseSupplierId) {
                $query->where('purchases.supplier_id', $purchaseSupplierId);
            }
        });

        $query->orderBy('purchases.date', 'desc')
            ->orderBy('purchase_global_discounts.sequence', 'asc')
            ->orderBy('purchase_global_discounts.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($purchaseCode) ? '[empty]' : $purchaseCode,
                    $purchaseStartDate ?? '[null]',
                    $purchaseEndDate ?? '[null]',
                    $purchaseSupplierId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_global_discount_'.implode('_', $cacheParams);

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

    public function read(PurchaseGlobalDiscount $purchaseGlobalDiscount): PurchaseGlobalDiscount
    {
        return $purchaseGlobalDiscount->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseGlobalDiscountCreateDTO $data): PurchaseGlobalDiscount
    {
        $timer_start = microtime(true);

        try {
            $purchaseGlobalDiscount = new PurchaseGlobalDiscount();
            $purchaseGlobalDiscount->company_id = $data->companyId;
            $purchaseGlobalDiscount->branch_id = $data->branchId;
            $purchaseGlobalDiscount->purchase_id = $data->purchaseId;
            $purchaseGlobalDiscount->sequence = $data->sequence;
            $purchaseGlobalDiscount->discount_type = $data->discountType;
            $purchaseGlobalDiscount->discount_value = $data->discountValue;
            $purchaseGlobalDiscount->save();

            $this->flushCache();

            return $purchaseGlobalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseGlobalDiscount $purchaseGlobalDiscount, PurchaseGlobalDiscountUpdateDTO $data): PurchaseGlobalDiscount
    {
        $timer_start = microtime(true);

        try {
            $purchaseGlobalDiscount->sequence = $data->sequence;
            $purchaseGlobalDiscount->discount_type = $data->discountType;
            $purchaseGlobalDiscount->discount_value = $data->discountValue;
            $purchaseGlobalDiscount->save();

            $this->flushCache();

            return $purchaseGlobalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseGlobalDiscount $purchaseGlobalDiscount): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $purchaseGlobalDiscount->delete();

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
