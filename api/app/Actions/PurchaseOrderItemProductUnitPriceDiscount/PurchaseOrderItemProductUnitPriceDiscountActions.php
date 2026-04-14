<?php

namespace App\Actions\PurchaseOrderItemProductUnitPriceDiscount;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderItemProductUnitPriceDiscountCreateDTO;
use App\DTOs\PurchaseOrderItemProductUnitPriceDiscountUpdateDTO;
use App\Enums\DiscountTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderItemProductUnitPriceDiscount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderItemProductUnitPriceDiscountActions
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
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderItemProductUnitPriceDiscount::select('purchase_order_item_product_unit_price_discounts.*')
            ->with([
                'company',
                'branch',
                'purchaseOrderItem.purchaseOrder.supplier',
                'purchaseOrderItem.productUnit.unit',
                'purchaseOrderItem.productUnit.product.category',
                'purchaseOrderItem.productUnit.product.brand',
                'purchaseOrderItem.productUnit.product.baseProductUnit.unit',
                'purchaseOrderItem.productUnit.product.images',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_order_item_product_unit_price_discounts.company_id')
            ->join('purchase_order_items', 'purchase_order_items.id', '=', 'purchase_order_item_product_unit_price_discounts.purchase_order_item_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_order_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('purchase_order_item_product_unit_price_discounts', $companyId)
            ->whereBranchId('purchase_order_item_product_unit_price_discounts', $branchId)
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
            ->orderBy('purchase_order_item_product_unit_price_discounts.sequence', 'asc')
            ->orderBy('purchase_order_item_product_unit_price_discounts.id', 'asc');

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

                $cacheKey = 'read_any_purchase_order_item_product_unit_price_discount_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderItemProductUnitPriceDiscount $poItemPriceDiscount): PurchaseOrderItemProductUnitPriceDiscount
    {
        return $poItemPriceDiscount->load([
            'company',
            'branch',
            'purchaseOrderItem.purchaseOrder.supplier',
            'purchaseOrderItem.productUnit.unit',
            'purchaseOrderItem.productUnit.product.category',
            'purchaseOrderItem.productUnit.product.brand',
            'purchaseOrderItem.productUnit.product.baseProductUnit.unit',
            'purchaseOrderItem.productUnit.product.images',
        ]);
    }

    public function getAmountByPurchaseOrderItemId(int $purchaseOrderItemId): float
    {
        $purchaseOrderItem = PurchaseOrderItem::query()
            ->with(['productUnitPriceDiscounts' => fn ($query) => $query->orderBy('sequence')->orderBy('id')])
            ->findOrFail($purchaseOrderItemId);

        $beforeDiscount = (float) $purchaseOrderItem->product_unit_price;
        $afterDiscount = $beforeDiscount;

        foreach ($purchaseOrderItem->productUnitPriceDiscounts as $discount) {
            $discountType = $discount->discount_type instanceof DiscountTypeEnum
                ? $discount->discount_type
                : DiscountTypeEnum::resolveToEnum($discount->discount_type);
            $discountValue = (float) $discount->discount_value;

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

    public function create(PurchaseOrderItemProductUnitPriceDiscountCreateDTO $data): PurchaseOrderItemProductUnitPriceDiscount
    {
        $timer_start = microtime(true);

        try {
            $poItemPriceDiscount = new PurchaseOrderItemProductUnitPriceDiscount();
            $poItemPriceDiscount->company_id = $data->companyId;
            $poItemPriceDiscount->branch_id = $data->branchId;
            $poItemPriceDiscount->purchase_order_item_id = $data->purchaseOrderItemId;
            $poItemPriceDiscount->sequence = $data->sequence;
            $poItemPriceDiscount->discount_type = $data->discountType;
            $poItemPriceDiscount->discount_value = $data->discountValue;
            $poItemPriceDiscount->save();

            $this->flushCache();

            return $poItemPriceDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderItemProductUnitPriceDiscount $poItemPriceDiscount, PurchaseOrderItemProductUnitPriceDiscountUpdateDTO $data): PurchaseOrderItemProductUnitPriceDiscount
    {
        $timer_start = microtime(true);

        try {
            $poItemPriceDiscount->sequence = $data->sequence;
            $poItemPriceDiscount->discount_type = $data->discountType;
            $poItemPriceDiscount->discount_value = $data->discountValue;
            $poItemPriceDiscount->save();

            $this->flushCache();

            return $poItemPriceDiscount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderItemProductUnitPriceDiscount $poItemPriceDiscount): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $poItemPriceDiscount->delete();

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
