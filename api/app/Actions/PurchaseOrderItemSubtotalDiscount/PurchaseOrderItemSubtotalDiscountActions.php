<?php

namespace App\Actions\PurchaseOrderItemSubtotalDiscount;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderItemSubtotalDiscountCreateDTO;
use App\DTOs\PurchaseOrderItemSubtotalDiscountUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderItemSubtotalDiscount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderItemSubtotalDiscountActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseOrderItem.purchaseOrder.supplier',
        'purchaseOrderItem.productUnit.unit',
        'purchaseOrderItem.productUnit.product.category',
        'purchaseOrderItem.productUnit.product.brand',
        'purchaseOrderItem.productUnit.product.baseProductUnit.unit',
        'purchaseOrderItem.productUnit.product.images',
        'purchaseOrderItem.productUnit.product.mainImage',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $purchaseOrderCode,
        ?string $purchaseOrderStartDate,
        ?string $purchaseOrderEndDate,
        ?int $purchaseOrderSupplierId,
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderItemSubtotalDiscount::select('purchase_order_item_subtotal_discounts.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_order_item_subtotal_discounts.company_id')
            ->join('purchase_order_items', 'purchase_order_items.id', '=', 'purchase_order_item_subtotal_discounts.purchase_order_item_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_order_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('purchase_order_item_subtotal_discounts', $companyId)
            ->whereBranchId('purchase_order_item_subtotal_discounts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseOrderCode,
            $purchaseOrderStartDate,
            $purchaseOrderEndDate,
            $purchaseOrderSupplierId,
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

        $query->orderBy('purchase_orders.date', 'desc')
            ->orderBy('purchase_order_items.id', 'asc')
            ->orderBy('purchase_order_item_subtotal_discounts.sequence', 'asc')
            ->orderBy('purchase_order_item_subtotal_discounts.id', 'asc');

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
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_item_subtotal_discount_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderItemSubtotalDiscount $poItemSubtotalDiscount): PurchaseOrderItemSubtotalDiscount
    {
        return $poItemSubtotalDiscount->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseOrderItemSubtotalDiscountCreateDTO $data): PurchaseOrderItemSubtotalDiscount
    {
        $timer_start = microtime(true);

        try {
            $poItemSubtotalDiscount = new PurchaseOrderItemSubtotalDiscount();
            $poItemSubtotalDiscount->company_id = $data->companyId;
            $poItemSubtotalDiscount->branch_id = $data->branchId;
            $poItemSubtotalDiscount->purchase_order_item_id = $data->purchaseOrderItemId;
            $poItemSubtotalDiscount->sequence = $data->sequence;
            $poItemSubtotalDiscount->discount_type = $data->discountType;
            $poItemSubtotalDiscount->discount_value = $data->discountValue;
            $poItemSubtotalDiscount->save();

            $this->flushCache();

            return $poItemSubtotalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderItemSubtotalDiscount $poItemSubtotalDiscount, PurchaseOrderItemSubtotalDiscountUpdateDTO $data): PurchaseOrderItemSubtotalDiscount
    {
        $timer_start = microtime(true);

        try {
            $poItemSubtotalDiscount->sequence = $data->sequence;
            $poItemSubtotalDiscount->discount_type = $data->discountType;
            $poItemSubtotalDiscount->discount_value = $data->discountValue;
            $poItemSubtotalDiscount->save();

            $this->flushCache();

            return $poItemSubtotalDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderItemSubtotalDiscount $poItemSubtotalDiscount): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $poItemSubtotalDiscount->delete();

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
