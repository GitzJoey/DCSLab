<?php

namespace App\Actions\PurchaseItemProductUnitPriceDiscount;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseItemProductUnitPriceDiscountCreateDTO;
use App\DTOs\PurchaseItemProductUnitPriceDiscountUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseItemProductUnitPriceDiscount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseItemProductUnitPriceDiscountActions
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
        $query = PurchaseItemProductUnitPriceDiscount::select('purchase_item_product_unit_price_discounts.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_item_product_unit_price_discounts.company_id')
            ->join('purchase_items', 'purchase_items.id', '=', 'purchase_item_product_unit_price_discounts.purchase_item_id')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('purchase_item_product_unit_price_discounts', $companyId)
            ->whereBranchId('purchase_item_product_unit_price_discounts', $branchId)
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
                $query->search($search);
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
            ->orderBy('purchase_item_product_unit_price_discounts.sequence', 'asc')
            ->orderBy('purchase_item_product_unit_price_discounts.id', 'asc');

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

                $cacheKey = 'read_any_purchase_item_product_unit_price_discount_'.implode('_', $cacheParams);

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

    public function read(PurchaseItemProductUnitPriceDiscount $purchaseItemPriceDiscount): PurchaseItemProductUnitPriceDiscount
    {
        return $purchaseItemPriceDiscount->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseItemProductUnitPriceDiscountCreateDTO $data): PurchaseItemProductUnitPriceDiscount
    {
        $timer_start = microtime(true);

        try {
            $purchaseItemPriceDiscount = new PurchaseItemProductUnitPriceDiscount();
            $purchaseItemPriceDiscount->company_id = $data->companyId;
            $purchaseItemPriceDiscount->branch_id = $data->branchId;
            $purchaseItemPriceDiscount->purchase_item_id = $data->purchaseItemId;
            $purchaseItemPriceDiscount->sequence = $data->sequence;
            $purchaseItemPriceDiscount->discount_type = $data->discountType;
            $purchaseItemPriceDiscount->discount_value = $data->discountValue;
            $purchaseItemPriceDiscount->save();

            $this->flushCache();

            return $purchaseItemPriceDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PurchaseItemProductUnitPriceDiscount $purchaseItemPriceDiscount,
        PurchaseItemProductUnitPriceDiscountUpdateDTO $data
    ): PurchaseItemProductUnitPriceDiscount {
        $timer_start = microtime(true);

        try {
            $purchaseItemPriceDiscount->sequence = $data->sequence;
            $purchaseItemPriceDiscount->discount_type = $data->discountType;
            $purchaseItemPriceDiscount->discount_value = $data->discountValue;
            $purchaseItemPriceDiscount->save();

            $this->flushCache();

            return $purchaseItemPriceDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseItemProductUnitPriceDiscount $purchaseItemPriceDiscount): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $purchaseItemPriceDiscount->delete();

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
