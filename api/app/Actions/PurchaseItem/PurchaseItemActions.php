<?php

namespace App\Actions\PurchaseItem;

use App\Actions\Purchase\PurchaseActions;
use App\Actions\PurchaseItemProductUnitPriceDiscount\PurchaseItemProductUnitPriceDiscountActions;
use App\Actions\PurchaseItemSubtotalDiscount\PurchaseItemSubtotalDiscountActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseItemCreateDTO;
use App\DTOs\PurchaseItemProductUnitPriceDiscountCreateDTO;
use App\DTOs\PurchaseItemProductUnitPriceDiscountUpdateDTO;
use App\DTOs\PurchaseItemSubtotalDiscountCreateDTO;
use App\DTOs\PurchaseItemSubtotalDiscountUpdateDTO;
use App\DTOs\PurchaseItemUpdateDTO;
use App\Enums\DiscountTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchase.supplier',
        'purchaseOrderItem.purchaseOrder.supplier',
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

    public function __construct(
        private readonly PurchaseItemProductUnitPriceDiscountActions $purchaseItemProductUnitPriceDiscountActions,
        private readonly PurchaseItemSubtotalDiscountActions $purchaseItemSubtotalDiscountActions,
    ) {
    }

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
        $query = PurchaseItem::select('purchase_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_items.company_id')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('product_units', 'product_units.id', '=', 'purchase_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('purchase_items', $companyId)
            ->whereBranchId('purchase_items', $branchId)
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
                    $query->where('purchase_items.remarks', 'like', '%'.$search.'%');
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
            ->orderBy('purchase_items.id', 'asc');

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

                $cacheKey = 'read_any_purchase_item_'.implode('_', $cacheParams);

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

    public function read(PurchaseItem $purchaseItem): PurchaseItem
    {
        return $purchaseItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseItemCreateDTO $data, bool $updateParentSummary): PurchaseItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseItem = new PurchaseItem();
            $purchaseItem->company_id = $data->companyId;
            $purchaseItem->branch_id = $data->branchId;
            $purchaseItem->purchase_id = $data->purchaseId;
            $purchaseItem->purchase_order_item_id = $data->purchaseOrderItemId;
            $purchaseItem->qty = $data->qty;
            $purchaseItem->product_unit_id = $data->productUnitId;
            $purchaseItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $purchaseItem->product_unit_price = $data->productUnitPrice;
            $purchaseItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $purchaseItem->vat_profile_id = $data->vatProfileId;
            $purchaseItem->vat_rate = $data->vatRate;
            $purchaseItem->vat_base_numerator = $data->vatBaseNumerator;
            $purchaseItem->vat_base_denominator = $data->vatBaseDenominator;
            $purchaseItem->remarks = $data->remarks;
            $purchaseItem->save();

            foreach ($data->productUnitPriceDiscounts as $discount) {
                $dto = new PurchaseItemProductUnitPriceDiscountCreateDTO(
                    companyId: $purchaseItem->company_id,
                    branchId: $purchaseItem->branch_id,
                    purchaseItemId: $purchaseItem->id,
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseItemProductUnitPriceDiscountActions->create($dto);
            }

            foreach ($data->subtotalDiscounts as $discount) {
                $dto = new PurchaseItemSubtotalDiscountCreateDTO(
                    companyId: $purchaseItem->company_id,
                    branchId: $purchaseItem->branch_id,
                    purchaseItemId: $purchaseItem->id,
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseItemSubtotalDiscountActions->create($dto);
            }

            $purchaseItem->price_discount = (function () use ($purchaseItem) {
                $beforeDiscount = (float) $purchaseItem->product_unit_price;
                $afterDiscount = $beforeDiscount;

                foreach ($purchaseItem->productUnitPriceDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            $purchaseItem->price_after_discount = $purchaseItem->product_unit_price - $purchaseItem->price_discount;
            $purchaseItem->subtotal = $purchaseItem->qty * $purchaseItem->price_after_discount;
            $purchaseItem->subtotal_discount = (function () use ($purchaseItem) {
                $beforeDiscount = (float) $purchaseItem->subtotal;
                $afterDiscount = $beforeDiscount;

                foreach ($purchaseItem->subtotalDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            $purchaseItem->subtotal_after_discount = $purchaseItem->subtotal - $purchaseItem->subtotal_discount;
            $purchaseItem->save();

            if ($updateParentSummary) {
                PurchaseActions::updateSummary($purchaseItem->purchase);
                $purchaseItem->refresh();
            }

            $this->flushCache();

            return $purchaseItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseItem $purchaseItem, PurchaseItemUpdateDTO $data, bool $updateParentSummary): PurchaseItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseItem->purchase_order_item_id = $data->purchaseOrderItemId;
            $purchaseItem->qty = $data->qty;
            $purchaseItem->product_unit_id = $data->productUnitId;
            $purchaseItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $purchaseItem->product_unit_price = $data->productUnitPrice;
            $purchaseItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $purchaseItem->vat_profile_id = $data->vatProfileId;
            $purchaseItem->vat_rate = $data->vatRate;
            $purchaseItem->vat_base_numerator = $data->vatBaseNumerator;
            $purchaseItem->vat_base_denominator = $data->vatBaseDenominator;
            $purchaseItem->remarks = $data->remarks;

            foreach ($data->deleteProductUnitPriceDiscountIds as $deleteId) {
                $purchaseItemPriceDiscount = $purchaseItem->productUnitPriceDiscounts()->findOrFail($deleteId);
                $this->purchaseItemProductUnitPriceDiscountActions->delete($purchaseItemPriceDiscount);
            }

            foreach ($data->productUnitPriceDiscounts as $discount) {
                if (! empty($discount['id'])) {
                    $purchaseItemPriceDiscount = $purchaseItem->productUnitPriceDiscounts()->findOrFail($discount['id']);
                    $dto = new PurchaseItemProductUnitPriceDiscountUpdateDTO(
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseItemProductUnitPriceDiscountActions->update($purchaseItemPriceDiscount, $dto);
                } else {
                    $dto = new PurchaseItemProductUnitPriceDiscountCreateDTO(
                        companyId: $purchaseItem->company_id,
                        branchId: $purchaseItem->branch_id,
                        purchaseItemId: $purchaseItem->id,
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseItemProductUnitPriceDiscountActions->create($dto);
                }
            }

            foreach ($data->deleteSubtotalDiscountIds as $deleteId) {
                $purchaseItemSubtotalDiscount = $purchaseItem->subtotalDiscounts()->findOrFail($deleteId);
                $this->purchaseItemSubtotalDiscountActions->delete($purchaseItemSubtotalDiscount);
            }

            foreach ($data->subtotalDiscounts as $discount) {
                if (! empty($discount['id'])) {
                    $purchaseItemSubtotalDiscount = $purchaseItem->subtotalDiscounts()->findOrFail($discount['id']);
                    $dto = new PurchaseItemSubtotalDiscountUpdateDTO(
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseItemSubtotalDiscountActions->update($purchaseItemSubtotalDiscount, $dto);
                } else {
                    $dto = new PurchaseItemSubtotalDiscountCreateDTO(
                        companyId: $purchaseItem->company_id,
                        branchId: $purchaseItem->branch_id,
                        purchaseItemId: $purchaseItem->id,
                        sequence: $discount['sequence'],
                        discountType: $discount['discount_type'],
                        discountValue: $discount['discount_value'],
                    );

                    $this->purchaseItemSubtotalDiscountActions->create($dto);
                }
            }

            $purchaseItem->price_discount = (function () use ($purchaseItem) {
                $beforeDiscount = (float) $purchaseItem->product_unit_price;
                $afterDiscount = $beforeDiscount;

                foreach ($purchaseItem->productUnitPriceDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            $purchaseItem->price_after_discount = $purchaseItem->product_unit_price - $purchaseItem->price_discount;
            $purchaseItem->subtotal = $purchaseItem->qty * $purchaseItem->price_after_discount;
            $purchaseItem->subtotal_discount = (function () use ($purchaseItem) {
                $beforeDiscount = (float) $purchaseItem->subtotal;
                $afterDiscount = $beforeDiscount;

                foreach ($purchaseItem->subtotalDiscounts()->orderBy('sequence')->orderBy('id')->get() as $discount) {
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
            $purchaseItem->subtotal_after_discount = $purchaseItem->subtotal - $purchaseItem->subtotal_discount;
            $purchaseItem->save();

            if ($updateParentSummary) {
                PurchaseActions::updateSummary($purchaseItem->purchase);
                $purchaseItem->refresh();
            }

            $this->flushCache();

            return $purchaseItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseItem $purchaseItem): bool
    {
        $timer_start = microtime(true);

        try {
            foreach ($purchaseItem->productUnitPriceDiscounts as $purchaseItemPriceDiscount) {
                $this->purchaseItemProductUnitPriceDiscountActions->delete($purchaseItemPriceDiscount);
            }

            foreach ($purchaseItem->subtotalDiscounts as $purchaseItemSubtotalDiscount) {
                $this->purchaseItemSubtotalDiscountActions->delete($purchaseItemSubtotalDiscount);
            }

            $result = $purchaseItem->delete();

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
