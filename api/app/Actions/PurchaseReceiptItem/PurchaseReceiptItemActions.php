<?php

namespace App\Actions\PurchaseReceiptItem;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\Actions\PurchaseReceiptItemSerial\PurchaseReceiptItemSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseReceiptItemCreateDTO;
use App\DTOs\PurchaseReceiptItemSerialCreateDTO;
use App\DTOs\PurchaseReceiptItemSerialUpdateDTO;
use App\DTOs\PurchaseReceiptItemUpdateDTO;
use App\Models\PurchaseReceiptItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReceiptItemActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly PurchaseReceiptItemSerialActions $purchaseReceiptItemSerialActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseReceipt.supplier',
        'purchaseReceipt.purchase.supplier',
        'purchaseItem',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseReceipt.supplier',
        'purchaseReceipt.purchase.supplier',
        'purchaseItem',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
        'serials',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?int $purchaseReceiptId,
        ?int $purchaseItemId,
        ?int $productUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReceiptItem::select('purchase_receipt_items.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query->join('companies', 'companies.id', '=', 'purchase_receipt_items.company_id')
            ->join('purchase_receipts', 'purchase_receipts.id', '=', 'purchase_receipt_items.purchase_receipt_id')
            ->whereCompanyId('purchase_receipt_items', $companyId)
            ->whereBranchId('purchase_receipt_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseReceiptId,
            $purchaseItemId,
            $productUnitId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where('purchase_receipt_items.remarks', 'like', '%'.$search.'%');
            }

            if ($purchaseReceiptId) {
                $query->where('purchase_receipt_items.purchase_receipt_id', $purchaseReceiptId);
            }

            if ($purchaseItemId) {
                $query->where('purchase_receipt_items.purchase_item_id', $purchaseItemId);
            }

            if ($productUnitId) {
                $query->where('purchase_receipt_items.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('purchase_receipts.date', 'desc')
            ->orderBy('purchase_receipt_items.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseReceiptId ?? '[null]',
                    $purchaseItemId ?? '[null]',
                    $productUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_receipt_item_'.implode('_', $cacheParams);

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

    public function read(PurchaseReceiptItem $purchaseReceiptItem): PurchaseReceiptItem
    {
        return $purchaseReceiptItem->load(self::DETAIL_EAGER_LOADS);
    }

    public function create(PurchaseReceiptItemCreateDTO $data, bool $updateParent): PurchaseReceiptItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptItem = new PurchaseReceiptItem();
            $purchaseReceiptItem->company_id = $data->companyId;
            $purchaseReceiptItem->branch_id = $data->branchId;
            $purchaseReceiptItem->purchase_receipt_id = $data->purchaseReceiptId;
            $purchaseReceiptItem->purchase_item_id = $data->purchaseItemId;
            $purchaseReceiptItem->qty = $data->qty;
            $purchaseReceiptItem->product_unit_id = $data->productUnitId;
            $purchaseReceiptItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseReceiptItem->product_unit_qty_base = $data->productUnitQtyBase;
            $purchaseReceiptItem->remarks = $data->remarks;
            $purchaseReceiptItem->save();

            foreach ($data->serials as $serial) {
                $dto = new PurchaseReceiptItemSerialCreateDTO(
                    companyId: $purchaseReceiptItem->company_id,
                    branchId: $purchaseReceiptItem->branch_id,
                    purchaseReceiptId: $purchaseReceiptItem->purchase_receipt_id,
                    purchaseReceiptItemId: $purchaseReceiptItem->id,
                    serial: $serial['serial'],
                );

                $this->purchaseReceiptItemSerialActions->create($dto);
            }

            if ($updateParent) {
                PurchaseReceiptActions::updateSummary($purchaseReceiptItem->purchaseReceipt);
            }

            $this->flushCache();

            return $purchaseReceiptItem->refresh()->load(self::DETAIL_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReceiptItem $purchaseReceiptItem, PurchaseReceiptItemUpdateDTO $data, bool $updateParent): PurchaseReceiptItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptItem->purchase_item_id = $data->purchaseItemId;
            $purchaseReceiptItem->qty = $data->qty;
            $purchaseReceiptItem->product_unit_id = $data->productUnitId;
            $purchaseReceiptItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseReceiptItem->product_unit_qty_base = $data->productUnitQtyBase;
            $purchaseReceiptItem->remarks = $data->remarks;
            $purchaseReceiptItem->save();

            foreach ($data->deleteSerialIds as $deleteId) {
                $purchaseReceiptItemSerial = $purchaseReceiptItem->serials()->findOrFail($deleteId);
                $this->purchaseReceiptItemSerialActions->delete($purchaseReceiptItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $purchaseReceiptItemSerial = $purchaseReceiptItem->serials()->findOrFail($serial['id']);
                    $dto = new PurchaseReceiptItemSerialUpdateDTO(
                        serial: $serial['serial'],
                    );

                    $this->purchaseReceiptItemSerialActions->update($purchaseReceiptItemSerial, $dto);
                } else {
                    $dto = new PurchaseReceiptItemSerialCreateDTO(
                        companyId: $purchaseReceiptItem->company_id,
                        branchId: $purchaseReceiptItem->branch_id,
                        purchaseReceiptId: $purchaseReceiptItem->purchase_receipt_id,
                        purchaseReceiptItemId: $purchaseReceiptItem->id,
                        serial: $serial['serial'],
                    );

                    $this->purchaseReceiptItemSerialActions->create($dto);
                }
            }

            if ($updateParent) {
                PurchaseReceiptActions::updateSummary($purchaseReceiptItem->purchaseReceipt);
            }

            $this->flushCache();

            return $purchaseReceiptItem->refresh()->load(self::DETAIL_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReceiptItem $purchaseReceiptItem, bool $updateParent): bool
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceipt = $purchaseReceiptItem->purchaseReceipt;

            foreach ($purchaseReceiptItem->serials as $purchaseReceiptItemSerial) {
                $this->purchaseReceiptItemSerialActions->delete($purchaseReceiptItemSerial);
            }

            $result = $purchaseReceiptItem->delete();

            if ($updateParent) {
                PurchaseReceiptActions::updateSummary($purchaseReceipt);
            }

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
