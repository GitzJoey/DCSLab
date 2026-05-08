<?php

namespace App\Actions\PurchaseOrderItem;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Actions\PurchaseOrderItemProductUnitPriceDiscount\PurchaseOrderItemProductUnitPriceDiscountActions;
use App\Actions\PurchaseOrderItemSubtotalDiscount\PurchaseOrderItemSubtotalDiscountActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderItemCreateDTO;
use App\DTOs\PurchaseOrderItemProductUnitPriceDiscountCreateDTO;
use App\DTOs\PurchaseOrderItemProductUnitPriceDiscountUpdateDTO;
use App\DTOs\PurchaseOrderItemSubtotalDiscountCreateDTO;
use App\DTOs\PurchaseOrderItemSubtotalDiscountUpdateDTO;
use App\DTOs\PurchaseOrderItemUpdateDTO;
use App\Enums\DiscountTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\ProductUnit;
use App\Models\PurchaseOrderItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseOrder.supplier',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
        'vatProfile',
        'productUnitPriceDiscounts',
        'subtotalDiscounts',
    ];

    private $purchaseOrderItemProductUnitPriceDiscountActions;

    private $purchaseOrderItemSubtotalDiscountActions;

    public function __construct(
        PurchaseOrderItemProductUnitPriceDiscountActions $purchaseOrderItemProductUnitPriceDiscountActions,
        PurchaseOrderItemSubtotalDiscountActions $purchaseOrderItemSubtotalDiscountActions,
    ) {
        $this->purchaseOrderItemProductUnitPriceDiscountActions = $purchaseOrderItemProductUnitPriceDiscountActions;
        $this->purchaseOrderItemSubtotalDiscountActions = $purchaseOrderItemSubtotalDiscountActions;
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
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_order_items.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_order_items.product_unit_id')
            ->join('products', 'products.id', '=', 'purchase_order_items.product_id')
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
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_order_items.remarks', 'like', '%'.$search.'%');
                });
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
        return $poItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(
        PurchaseOrderItemCreateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderItem {
        $timer_start = microtime(true);

        try {
            $poItem = new PurchaseOrderItem();
            $poItem->company_id = $data->companyId;
            $poItem->branch_id = $data->branchId;
            $poItem->purchase_order_id = $data->purchaseOrderId;
            $poItem->qty = $data->qty;
            $poItem->product_unit_id = $data->productUnitId;
            $poItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
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

            foreach ($data->productUnitPriceDiscounts as $discount) {
                $dto = new PurchaseOrderItemProductUnitPriceDiscountCreateDTO(
                    companyId: $poItem->company_id,
                    branchId: $poItem->branch_id,
                    purchaseOrderItemId: $poItem->id,
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseOrderItemProductUnitPriceDiscountActions->create($dto);
            }

            foreach ($data->subtotalDiscounts as $discount) {
                $dto = new PurchaseOrderItemSubtotalDiscountCreateDTO(
                    companyId: $poItem->company_id,
                    branchId: $poItem->branch_id,
                    purchaseOrderItemId: $poItem->id,
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseOrderItemSubtotalDiscountActions->create($dto);
            }

            $poItem->price_discount = (function () use ($poItem) {
                $beforeDiscount = (float) $poItem->product_unit_price;
                $afterDiscount = $beforeDiscount;

                foreach ($poItem->productUnitPriceDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            })();
            $poItem->price_after_discount = $poItem->product_unit_price - $poItem->price_discount;
            $poItem->subtotal = $poItem->qty * $poItem->price_after_discount;
            $poItem->subtotal_discount = (function () use ($poItem) {
                $beforeDiscount = (float) $poItem->subtotal;
                $afterDiscount = $beforeDiscount;

                foreach ($poItem->subtotalDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            })();
            $poItem->subtotal_after_discount = $poItem->subtotal - $poItem->subtotal_discount;
            $poItem->save();

            if ($updateParentSummary) {
                PurchaseOrderActions::updateSummary($poItem->purchaseOrder);
                $poItem->refresh();
            }

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

    public function update(
        PurchaseOrderItem $poItem,
        PurchaseOrderItemUpdateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderItem {
        $timer_start = microtime(true);

        try {
            $poItem->qty = $data->qty;
            $poItem->product_unit_id = $data->productUnitId;
            $poItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $poItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $poItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $poItem->product_unit_price = $data->productUnitPrice;
            $poItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $poItem->vat_profile_id = $data->vatProfileId;
            $poItem->vat_rate = $data->vatRate;
            $poItem->vat_base_numerator = $data->vatBaseNumerator;
            $poItem->vat_base_denominator = $data->vatBaseDenominator;
            $poItem->remarks = $data->remarks;

            foreach ($data->deleteProductUnitPriceDiscountIds as $deleteId) {
                $poItemPriceDiscount = $poItem->productUnitPriceDiscounts()->findOrFail($deleteId);
                $this->purchaseOrderItemProductUnitPriceDiscountActions->delete($poItemPriceDiscount);
            }

            foreach ($data->productUnitPriceDiscounts as $discount) {
                if (! empty($discount['id'])) {
                    $poItemPriceDiscount = $poItem->productUnitPriceDiscounts()->findOrFail($discount['id']);
                    $dto = new PurchaseOrderItemProductUnitPriceDiscountUpdateDTO(
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseOrderItemProductUnitPriceDiscountActions->update($poItemPriceDiscount, $dto);
                } else {
                    $dto = new PurchaseOrderItemProductUnitPriceDiscountCreateDTO(
                        companyId: $poItem->company_id,
                        branchId: $poItem->branch_id,
                        purchaseOrderItemId: $poItem->id,
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseOrderItemProductUnitPriceDiscountActions->create($dto);
                }
            }

            foreach ($data->deleteSubtotalDiscountIds as $deleteId) {
                $poItemSubtotalDiscount = $poItem->subtotalDiscounts()->findOrFail($deleteId);
                $this->purchaseOrderItemSubtotalDiscountActions->delete($poItemSubtotalDiscount);
            }

            foreach ($data->subtotalDiscounts as $discount) {
                if (! empty($discount['id'])) {
                    $poItemSubtotalDiscount = $poItem->subtotalDiscounts()->findOrFail($discount['id']);
                    $dto = new PurchaseOrderItemSubtotalDiscountUpdateDTO(
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseOrderItemSubtotalDiscountActions->update($poItemSubtotalDiscount, $dto);
                } else {
                    $dto = new PurchaseOrderItemSubtotalDiscountCreateDTO(
                        companyId: $poItem->company_id,
                        branchId: $poItem->branch_id,
                        purchaseOrderItemId: $poItem->id,
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseOrderItemSubtotalDiscountActions->create($dto);
                }
            }

            $poItem->price_discount = (function () use ($poItem) {
                $beforeDiscount = (float) $poItem->product_unit_price;
                $afterDiscount = $beforeDiscount;

                foreach ($poItem->productUnitPriceDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            })();
            $poItem->price_after_discount = $poItem->product_unit_price - $poItem->price_discount;
            $poItem->subtotal = $poItem->qty * $poItem->price_after_discount;
            $poItem->subtotal_discount = (function () use ($poItem) {
                $beforeDiscount = (float) $poItem->subtotal;
                $afterDiscount = $beforeDiscount;

                foreach ($poItem->subtotalDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            })();
            $poItem->subtotal_after_discount = $poItem->subtotal - $poItem->subtotal_discount;
            $poItem->save();

            if ($updateParentSummary) {
                PurchaseOrderActions::updateSummary($poItem->purchaseOrder);
                $poItem->refresh();
            }

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
