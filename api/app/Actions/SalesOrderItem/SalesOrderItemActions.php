<?php

namespace App\Actions\SalesOrderItem;

use App\Actions\SalesOrder\SalesOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\SalesOrderItemCreateDTO;
use App\DTOs\SalesOrderItemUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\ProductUnit;
use App\Models\SalesOrderItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'salesOrder.customer',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
        'vatProfile',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $salesOrderCode,
        ?string $salesOrderStartDate,
        ?string $salesOrderEndDate,
        ?int $salesOrderCustomerId,
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderItem::select('sales_order_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'sales_order_items.company_id')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('product_units', 'product_units.id', '=', 'sales_order_items.product_unit_id')
            ->join('products', 'products.id', '=', 'sales_order_items.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('sales_order_items', $companyId)
            ->whereBranchId('sales_order_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $salesOrderCode,
            $salesOrderStartDate,
            $salesOrderEndDate,
            $salesOrderCustomerId,
            $productUnitCode,
            $productUnitProductName,
            $productUnitProductCategoryId,
            $productUnitProductBrandId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_order_items.remarks', 'like', '%'.$search.'%');
                });
            }

            $salesOrderStartDateUtc = $salesOrderStartDate ? TimezoneHelper::convertToUTC($salesOrderStartDate) : null;
            if ($salesOrderStartDateUtc) {
                $query->where('sales_orders.date', '>=', $salesOrderStartDateUtc);
            }

            $salesOrderEndDateUtc = $salesOrderEndDate ? TimezoneHelper::convertToUTC($salesOrderEndDate) : null;
            if ($salesOrderEndDateUtc) {
                $query->where('sales_orders.date', '<=', $salesOrderEndDateUtc);
            }

            if ($salesOrderCode) {
                $query->where('sales_orders.code', $salesOrderCode);
            }

            if ($salesOrderCustomerId) {
                $query->where('sales_orders.customer_id', $salesOrderCustomerId);
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

        $query->orderBy('sales_orders.date', 'desc')
            ->orderBy('sales_order_items.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($salesOrderCode) ? '[empty]' : $salesOrderCode,
                    $salesOrderStartDate ?? '[null]',
                    $salesOrderEndDate ?? '[null]',
                    $salesOrderCustomerId ?? '[null]',
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_item_'.implode('_', $cacheParams);

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

    public function read(SalesOrderItem $salesOrderItem): SalesOrderItem
    {
        return $salesOrderItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(
        SalesOrderItemCreateDTO $data,
        bool $updateParentSummary,
    ): SalesOrderItem {
        $timer_start = microtime(true);

        try {
            $salesOrderItem = new SalesOrderItem();
            $salesOrderItem->company_id = $data->companyId;
            $salesOrderItem->branch_id = $data->branchId;
            $salesOrderItem->sales_order_id = $data->salesOrderId;
            $salesOrderItem->qty = $data->qty;
            $salesOrderItem->product_unit_id = $data->productUnitId;
            $salesOrderItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesOrderItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesOrderItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $salesOrderItem->product_unit_price = $data->productUnitPrice;
            $salesOrderItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $salesOrderItem->vat_profile_id = $data->vatProfileId;
            $salesOrderItem->vat_rate = $data->vatRate;
            $salesOrderItem->vat_base_numerator = $data->vatBaseNumerator;
            $salesOrderItem->vat_base_denominator = $data->vatBaseDenominator;
            $salesOrderItem->remarks = $data->remarks;

            $this->applyDiscountWaterfall($salesOrderItem, $data->priceDiscount, $data->subtotalDiscount);

            $salesOrderItem->save();

            if ($updateParentSummary) {
                SalesOrderActions::updateSummary($salesOrderItem->salesOrder);
                $salesOrderItem->refresh();
            }

            $this->flushCache();

            return $salesOrderItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        SalesOrderItem $salesOrderItem,
        SalesOrderItemUpdateDTO $data,
        bool $updateParentSummary,
    ): SalesOrderItem {
        $timer_start = microtime(true);

        try {
            $salesOrderItem->qty = $data->qty;
            $salesOrderItem->product_unit_id = $data->productUnitId;
            $salesOrderItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesOrderItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesOrderItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $salesOrderItem->product_unit_price = $data->productUnitPrice;
            $salesOrderItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $salesOrderItem->vat_profile_id = $data->vatProfileId;
            $salesOrderItem->vat_rate = $data->vatRate;
            $salesOrderItem->vat_base_numerator = $data->vatBaseNumerator;
            $salesOrderItem->vat_base_denominator = $data->vatBaseDenominator;
            $salesOrderItem->remarks = $data->remarks;

            $this->applyDiscountWaterfall($salesOrderItem, $data->priceDiscount, $data->subtotalDiscount);

            $salesOrderItem->save();

            if ($updateParentSummary) {
                SalesOrderActions::updateSummary($salesOrderItem->salesOrder);
                $salesOrderItem->refresh();
            }

            $this->flushCache();

            return $salesOrderItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function applyDiscountWaterfall(
        SalesOrderItem $salesOrderItem,
        float $priceDiscount,
        float $subtotalDiscount,
    ): void {
        $salesOrderItem->price_discount = min(
            max($priceDiscount, 0),
            max((float) $salesOrderItem->product_unit_price, 0)
        );
        $salesOrderItem->price_after_discount = max(
            0,
            (float) $salesOrderItem->product_unit_price - (float) $salesOrderItem->price_discount
        );
        $salesOrderItem->subtotal = (float) $salesOrderItem->qty * (float) $salesOrderItem->price_after_discount;
        $salesOrderItem->subtotal_discount = min(
            max($subtotalDiscount, 0),
            max((float) $salesOrderItem->subtotal, 0)
        );
        $salesOrderItem->subtotal_after_discount = max(
            0,
            (float) $salesOrderItem->subtotal - (float) $salesOrderItem->subtotal_discount
        );
    }

    public function delete(SalesOrderItem $salesOrderItem): bool
    {
        $timer_start = microtime(true);

        try {
            if ((float) $salesOrderItem->qty_delivered_base > 0 || (float) $salesOrderItem->qty_invoiced_base > 0) {
                throw new Exception(trans('rules.sales_order.invalid_delete_item_with_transactions'));
            }

            $result = $salesOrderItem->delete();

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
