<?php

namespace App\Actions\PurchaseItemSubtotalDiscount;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseItemSubtotalDiscountCreateDTO;
use App\DTOs\PurchaseItemSubtotalDiscountUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseItemSubtotalDiscount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseItemSubtotalDiscountActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseItem.purchase.supplier',
        'purchaseItem.productUnit.unit',
        'purchaseItem.productUnit.product.category',
        'purchaseItem.productUnit.product.brand',
        'purchaseItem.productUnit.product.baseProductUnit.unit',
        'purchaseItem.productUnit.product.images',
        'purchaseItem.productUnit.product.mainImage',
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
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseItemSubtotalDiscount::select('purchase_item_subtotal_discounts.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_item_subtotal_discounts.company_id')
            ->join('purchase_items', 'purchase_items.id', '=', 'purchase_item_subtotal_discounts.purchase_item_id')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('purchase_item_subtotal_discounts', $companyId)
            ->whereBranchId('purchase_item_subtotal_discounts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseCode,
            $purchaseStartDate,
            $purchaseEndDate,
            $purchaseSupplierId,
            $productUnitCode,
            $productUnitProductName,
            $productUnitProductCategoryId,
            $productUnitProductBrandId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_item_subtotal_discounts.discount_type', 'like', '%'.$search.'%');
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

            if ($productUnitCode) {
                $query->where('product_units.code', 'like', '%'.$productUnitCode.'%');
            }

            if ($productUnitProductName) {
                $query->where('products.name', 'like', '%'.$productUnitProductName.'%');
            }

            if ($productUnitProductCategoryId) {
                $query->where('products.category_id', $productUnitProductCategoryId);
            }

            if ($productUnitProductBrandId) {
                $query->where('products.brand_id', $productUnitProductBrandId);
            }
        });

        $query->orderBy('purchases.date', 'desc')
            ->orderBy('purchase_items.id', 'asc')
            ->orderBy('purchase_item_subtotal_discounts.sequence', 'asc')
            ->orderBy('purchase_item_subtotal_discounts.id', 'asc');

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
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_item_subtotal_discount_'.implode('_', $cacheParams);

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

    public function read(PurchaseItemSubtotalDiscount $purchaseItemSubtotalDiscount): PurchaseItemSubtotalDiscount
    {
        return $purchaseItemSubtotalDiscount->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseItemSubtotalDiscountCreateDTO $data): PurchaseItemSubtotalDiscount
    {
        $timer_start = microtime(true);

        try {
            $purchaseItemSubtotalDiscount = new PurchaseItemSubtotalDiscount();
            $purchaseItemSubtotalDiscount->company_id = $data->companyId;
            $purchaseItemSubtotalDiscount->branch_id = $data->branchId;
            $purchaseItemSubtotalDiscount->purchase_item_id = $data->purchaseItemId;
            $purchaseItemSubtotalDiscount->sequence = $data->sequence;
            $purchaseItemSubtotalDiscount->discount_type = $data->discountType;
            $purchaseItemSubtotalDiscount->discount_value = $data->discountValue;
            $purchaseItemSubtotalDiscount->save();

            $this->flushCache();

            return $purchaseItemSubtotalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PurchaseItemSubtotalDiscount $purchaseItemSubtotalDiscount,
        PurchaseItemSubtotalDiscountUpdateDTO $data
    ): PurchaseItemSubtotalDiscount {
        $timer_start = microtime(true);

        try {
            $purchaseItemSubtotalDiscount->sequence = $data->sequence;
            $purchaseItemSubtotalDiscount->discount_type = $data->discountType;
            $purchaseItemSubtotalDiscount->discount_value = $data->discountValue;
            $purchaseItemSubtotalDiscount->save();

            $this->flushCache();

            return $purchaseItemSubtotalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseItemSubtotalDiscount $purchaseItemSubtotalDiscount): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $purchaseItemSubtotalDiscount->delete();

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
