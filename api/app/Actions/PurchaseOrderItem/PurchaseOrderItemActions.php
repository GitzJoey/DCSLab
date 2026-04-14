<?php

namespace App\Actions\PurchaseOrderItem;

use App\Actions\PurchaseOrderItemProductUnitPriceDiscount\PurchaseOrderItemProductUnitPriceDiscountActions;
use App\Actions\PurchaseOrderItemSubtotalDiscount\PurchaseOrderItemSubtotalDiscountActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderItemCreateDTO;
use App\DTOs\PurchaseOrderItemUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\PurchaseOrderItem\PurchaseOrderItemDiscountService;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private $purchaseOrderItemProductUnitPriceDiscountActions;

    private $purchaseOrderItemSubtotalDiscountActions;

    private $purchaseOrderItemDiscountService;

    public function __construct(
        PurchaseOrderItemProductUnitPriceDiscountActions $purchaseOrderItemProductUnitPriceDiscountActions,
        PurchaseOrderItemSubtotalDiscountActions $purchaseOrderItemSubtotalDiscountActions,
        PurchaseOrderItemDiscountService $purchaseOrderItemDiscountService,
    ) {
        $this->purchaseOrderItemProductUnitPriceDiscountActions = $purchaseOrderItemProductUnitPriceDiscountActions;
        $this->purchaseOrderItemSubtotalDiscountActions = $purchaseOrderItemSubtotalDiscountActions;
        $this->purchaseOrderItemDiscountService = $purchaseOrderItemDiscountService;
    }

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
        $query = PurchaseOrderItem::select('purchase_order_items.*')
            ->with([
                'company',
                'branch',
                'purchaseOrder.supplier',
                'productUnit.unit',
                'productUnit.product.category',
                'productUnit.product.brand',
                'productUnit.product.baseProductUnit.unit',
                'productUnit.product.images',
                'vatProfile',
                'productUnitPriceDiscounts',
                'subtotalDiscounts',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_order_items.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_order_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('purchase_order_items', $companyId)
            ->whereBranchId('purchase_order_items', $branchId)
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
            ->orderBy('purchase_order_items.id', 'asc');

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

                $cacheKey = 'read_any_purchase_order_item_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderItem $poItem): PurchaseOrderItem
    {
        return $poItem->load([
            'company',
            'branch',
            'purchaseOrder.supplier',
            'productUnit.unit',
            'productUnit.product.category',
            'productUnit.product.brand',
            'productUnit.product.baseProductUnit.unit',
            'productUnit.product.images',
            'vatProfile',
            'productUnitPriceDiscounts',
            'subtotalDiscounts',
        ]);
    }

    public function getSubtotalAfterDiscountAmountByPurchaseOrderId(int $purchaseOrderId): float
    {
        return (float) PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchaseOrderId)
            ->sum('subtotal_after_discount');
    }

    public function create(PurchaseOrderItemCreateDTO $data): PurchaseOrderItem
    {
        $timer_start = microtime(true);

        try {
            $poItem = new PurchaseOrderItem();
            $poItem->company_id = $data->companyId;
            $poItem->branch_id = $data->branchId;
            $poItem->purchase_order_id = $data->purchaseOrderId;
            $poItem->qty = $data->qty;
            $poItem->product_unit_id = $data->productUnitId;
            $poItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $poItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $poItem->product_unit_price = $data->productUnitPrice;
            $poItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $poItem->vat_profile_id = $data->vatProfileId;
            $poItem->vat_rate = $data->vatRate;
            $poItem->vat_base_numerator = $data->vatBaseNumerator;
            $poItem->vat_base_denominator = $data->vatBaseDenominator;
            $poItem->remarks = $data->remarks;
            $poItem->save();

            $this->purchaseOrderItemDiscountService->createProductUnitPriceDiscounts($poItem, $data->productUnitPriceDiscounts);
            $this->purchaseOrderItemDiscountService->createSubtotalDiscounts($poItem, $data->subtotalDiscounts);

            $poItem->price_discount = $this->purchaseOrderItemProductUnitPriceDiscountActions->getAmountByPurchaseOrderItemId($poItem->id);
            $poItem->price_after_discount = $poItem->product_unit_price - $poItem->price_discount;
            $poItem->subtotal = $poItem->qty * $poItem->price_after_discount;
            $poItem->subtotal_discount = $this->purchaseOrderItemSubtotalDiscountActions->getAmountByPurchaseOrderItemId($poItem->id);
            $poItem->subtotal_after_discount = $poItem->subtotal - $poItem->subtotal_discount;

            $poItem->save();

            $this->flushCache();

            return $poItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderItem $poItem, PurchaseOrderItemUpdateDTO $data): PurchaseOrderItem
    {
        $timer_start = microtime(true);

        try {
            $poItem->qty = $data->qty;
            $poItem->product_unit_id = $data->productUnitId;
            $poItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $poItem->product_unit_price = $data->productUnitPrice;
            $poItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $poItem->vat_profile_id = $data->vatProfileId;
            $poItem->vat_rate = $data->vatRate;
            $poItem->vat_base_numerator = $data->vatBaseNumerator;
            $poItem->vat_base_denominator = $data->vatBaseDenominator;
            $poItem->remarks = $data->remarks;

            $this->purchaseOrderItemDiscountService->syncProductUnitPriceDiscounts($poItem, $data->deleteProductUnitPriceDiscountIds, $data->productUnitPriceDiscounts);
            $this->purchaseOrderItemDiscountService->syncSubtotalDiscounts($poItem, $data->deleteSubtotalDiscountIds, $data->subtotalDiscounts);

            $poItem->save();

            $this->flushCache();

            return $poItem->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function updateProductUnitGlobalDiscountByPurchaseOrderId(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with('items')
            ->findOrFail($purchaseOrderId);

        $itemTotalBeforeGlobalDiscount = (float) $purchaseOrder->item_total_before_global_discount;
        $globalDiscount = (float) $purchaseOrder->global_discount;

        foreach ($purchaseOrder->items as $poItem) {
            if ($itemTotalBeforeGlobalDiscount <= 0 || $globalDiscount <= 0) {
                $poItem->global_discount = 0;
            } else {
                $poItem->global_discount = ($poItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $globalDiscount;
            }

            if ($poItem->global_discount < 0) {
                $poItem->global_discount = 0;
            }

            $poItem->save();
        }
    }

    public function delete(PurchaseOrderItem $poItem): bool
    {
        $timer_start = microtime(true);

        try {
            foreach ($poItem->productUnitPriceDiscounts as $poItemPriceDiscount) {
                $this->purchaseOrderItemProductUnitPriceDiscountActions->delete($poItemPriceDiscount);
            }

            foreach ($poItem->subtotalDiscounts as $poItemSubtotalDiscount) {
                $this->purchaseOrderItemSubtotalDiscountActions->delete($poItemSubtotalDiscount);
            }

            $result = $poItem->delete();

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
