<?php

namespace App\Actions\PurchaseReturnItem;

use App\Actions\PurchaseReturnItemSerial\PurchaseReturnItemSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseReturnItemCreateDTO;
use App\DTOs\PurchaseReturnItemSerialCreateDTO;
use App\DTOs\PurchaseReturnItemSerialUpdateDTO;
use App\DTOs\PurchaseReturnItemUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Models\ProductUnit;
use App\Models\PurchaseReturnItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReturnItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseReturn.supplier',
        'purchaseReturn.warehouse',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
        'vatProfile',
        'purchaseOrderReceiptItem',
        'itemSerials',
    ];

    public function __construct(
        private readonly StockTransactionActions $stockTransactionActions,
        private readonly PurchaseReturnItemSerialActions $purchaseReturnItemSerialActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $purchaseReturnId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReturnItem::select('purchase_return_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_return_items.company_id')
            ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->whereCompanyId('purchase_return_items', $companyId)
            ->whereBranchId('purchase_return_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseReturnId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where('purchase_return_items.remarks', 'like', '%'.$search.'%');
            }

            if ($purchaseReturnId) {
                $query->where('purchase_return_items.purchase_return_id', $purchaseReturnId);
            }
        });

        $query->orderBy('purchase_returns.date', 'desc')
            ->orderBy('purchase_return_items.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseReturnId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_return_item_'.implode('_', $cacheParams);

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

    public function read(PurchaseReturnItem $purchaseReturnItem): PurchaseReturnItem
    {
        return $purchaseReturnItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseReturnItemCreateDTO $data): PurchaseReturnItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnItem = new PurchaseReturnItem();
            $purchaseReturnItem->company_id = $data->companyId;
            $purchaseReturnItem->branch_id = $data->branchId;
            $purchaseReturnItem->purchase_return_id = $data->purchaseReturnId;
            $purchaseReturnItem->purchase_order_receipt_item_id = $data->purchaseOrderReceiptItemId;
            $purchaseReturnItem->qty = $data->qty;
            $purchaseReturnItem->product_unit_id = $data->productUnitId;
            $purchaseReturnItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $purchaseReturnItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseReturnItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $purchaseReturnItem->product_unit_price = $data->productUnitPrice;
            $purchaseReturnItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $purchaseReturnItem->vat_profile_id = $data->vatProfileId;
            $purchaseReturnItem->vat_rate = $data->vatRate;
            $purchaseReturnItem->vat_base_numerator = $data->vatBaseNumerator;
            $purchaseReturnItem->vat_base_denominator = $data->vatBaseDenominator;
            $purchaseReturnItem->remarks = $data->remarks;

            $this->computeItemPricing($purchaseReturnItem, $data->priceDiscount, $data->subtotalDiscount);

            $purchaseReturnItem->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromPurchaseReturnItem($purchaseReturnItem)
            );

            foreach ($data->serials as $serial) {
                $dto = new PurchaseReturnItemSerialCreateDTO(
                    companyId: $purchaseReturnItem->company_id,
                    branchId: $purchaseReturnItem->branch_id,
                    purchaseReturnId: $purchaseReturnItem->purchase_return_id,
                    purchaseReturnItemId: $purchaseReturnItem->id,
                    serial: $serial['serial'],
                );

                $this->purchaseReturnItemSerialActions->create($dto);
            }

            $this->flushCache();

            return $purchaseReturnItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReturnItem $purchaseReturnItem, PurchaseReturnItemUpdateDTO $data): PurchaseReturnItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnItem->purchase_order_receipt_item_id = $data->purchaseOrderReceiptItemId;
            $purchaseReturnItem->qty = $data->qty;
            $purchaseReturnItem->product_unit_id = $data->productUnitId;
            $purchaseReturnItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $purchaseReturnItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseReturnItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $purchaseReturnItem->product_unit_price = $data->productUnitPrice;
            $purchaseReturnItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $purchaseReturnItem->vat_profile_id = $data->vatProfileId;
            $purchaseReturnItem->vat_rate = $data->vatRate;
            $purchaseReturnItem->vat_base_numerator = $data->vatBaseNumerator;
            $purchaseReturnItem->vat_base_denominator = $data->vatBaseDenominator;
            $purchaseReturnItem->remarks = $data->remarks;

            $this->computeItemPricing($purchaseReturnItem, $data->priceDiscount, $data->subtotalDiscount);

            $purchaseReturnItem->save();

            $stockTransaction = $purchaseReturnItem->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromPurchaseReturnItem($purchaseReturnItem);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromPurchaseReturnItem($purchaseReturnItem);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $purchaseReturnItemSerial = $purchaseReturnItem->itemSerials()->findOrFail($deleteId);
                $this->purchaseReturnItemSerialActions->delete($purchaseReturnItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $purchaseReturnItemSerial = $purchaseReturnItem->itemSerials()->findOrFail($serial['id']);
                    $dto = new PurchaseReturnItemSerialUpdateDTO(
                        serial: $serial['serial'],
                    );

                    $this->purchaseReturnItemSerialActions->update($purchaseReturnItemSerial, $dto);
                } else {
                    $dto = new PurchaseReturnItemSerialCreateDTO(
                        companyId: $purchaseReturnItem->company_id,
                        branchId: $purchaseReturnItem->branch_id,
                        purchaseReturnId: $purchaseReturnItem->purchase_return_id,
                        purchaseReturnItemId: $purchaseReturnItem->id,
                        serial: $serial['serial'],
                    );

                    $this->purchaseReturnItemSerialActions->create($dto);
                }
            }

            $this->flushCache();

            return $purchaseReturnItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturnItem $purchaseReturnItem): bool
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = $purchaseReturnItem->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            foreach ($purchaseReturnItem->itemSerials as $purchaseReturnItemSerial) {
                $this->purchaseReturnItemSerialActions->delete($purchaseReturnItemSerial);
            }

            $result = $purchaseReturnItem->delete();

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

    private function computeItemPricing(PurchaseReturnItem $purchaseReturnItem, float $priceDiscount, float $subtotalDiscount): void
    {
        $productUnitPrice = (float) $purchaseReturnItem->product_unit_price;

        $purchaseReturnItem->price_discount = min(max(0, $priceDiscount), $productUnitPrice);
        $purchaseReturnItem->price_after_discount = $productUnitPrice - (float) $purchaseReturnItem->price_discount;
        $purchaseReturnItem->subtotal = (float) $purchaseReturnItem->qty * (float) $purchaseReturnItem->price_after_discount;
        $purchaseReturnItem->subtotal_discount = min(max(0, $subtotalDiscount), (float) $purchaseReturnItem->subtotal);
        $purchaseReturnItem->subtotal_after_discount = (float) $purchaseReturnItem->subtotal - (float) $purchaseReturnItem->subtotal_discount;
    }
}
