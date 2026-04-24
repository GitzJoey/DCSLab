<?php

namespace App\Actions\PurchaseReceipt;

use App\Actions\Purchase\PurchaseActions;
use App\Actions\PurchaseReceiptItem\PurchaseReceiptItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseReceiptCreateDTO;
use App\DTOs\PurchaseReceiptItemCreateDTO;
use App\DTOs\PurchaseReceiptUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseReceipt;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReceiptActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly PurchaseReceiptItemActions $purchaseReceiptItemActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchase.supplier',
        'warehouse',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchase.supplier',
        'warehouse',
        'items.purchaseItem',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.serials',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?int $purchaseId,
        ?int $warehouseId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReceipt::select('purchase_receipts.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'purchase_receipts.company_id')
            ->whereCompanyId('purchase_receipts', $companyId)
            ->whereBranchId('purchase_receipts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $purchaseId,
            $warehouseId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_receipts.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_receipts.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_receipts.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_receipts.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($purchaseId) {
                $query->where('purchase_receipts.purchase_id', $purchaseId);
            }

            if ($warehouseId) {
                $query->where('purchase_receipts.warehouse_id', $warehouseId);
            }
        });

        $query->orderBy('purchase_receipts.date', 'desc')
            ->orderBy('purchase_receipts.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $purchaseId ?? '[null]',
                    $warehouseId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_receipt_'.implode('_', $cacheParams);

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

    public function read(PurchaseReceipt $purchaseReceipt): PurchaseReceipt
    {
        return $purchaseReceipt->load(self::DETAIL_EAGER_LOADS);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $tryCount = 0;
        do {
            $count = PurchaseReceipt::withTrashed()->where('company_id', $companyId)->count() + 1 + $tryCount;
            $code = 'PRC'.str_pad($count, 5, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseReceipt::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    private function generateDate($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function create(PurchaseReceiptCreateDTO $data, bool $updateParent): PurchaseReceipt
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceipt = new PurchaseReceipt();
            $purchaseReceipt->company_id = $data->companyId;
            $purchaseReceipt->branch_id = $data->branchId;
            $purchaseReceipt->supplier_id = $data->supplierId;
            $purchaseReceipt->purchase_id = $data->purchaseId;
            $purchaseReceipt->is_from_direct_purchase = $data->isFromDirectPurchase;
            $purchaseReceipt->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseReceipt->date = $this->generateDate($data->date);
            $purchaseReceipt->warehouse_id = $data->warehouseId;
            $purchaseReceipt->remarks = $data->remarks;
            $purchaseReceipt->is_posted = $data->isPosted;
            $purchaseReceipt->save();

            foreach ($data->items as $item) {
                $dto = new PurchaseReceiptItemCreateDTO(
                    companyId: $purchaseReceipt->company_id,
                    branchId: $purchaseReceipt->branch_id,
                    purchaseReceiptId: $purchaseReceipt->id,
                    purchaseItemId: $item['purchase_item_id'],

                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitQtyBase: $item['product_unit_qty_base'],
                    remarks: $item['remarks'],

                    serials: $item['serials'],
                );

                $this->purchaseReceiptItemActions->create($dto, false);
            }

            self::updateSummary($purchaseReceipt);

            if ($purchaseReceipt->purchase && $updateParent) {
                PurchaseActions::updateSummary($purchaseReceipt->purchase);
            }

            $this->flushCache();

            return $purchaseReceipt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReceipt $purchaseReceipt, PurchaseReceiptUpdateDTO $data, bool $updateParent): PurchaseReceipt
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceipt->supplier_id = $data->supplierId;
            $purchaseReceipt->purchase_id = $data->purchaseId;
            $purchaseReceipt->code = $this->generateUniqueCode($purchaseReceipt->company_id, $data->code, $purchaseReceipt->id);
            $purchaseReceipt->date = $this->generateDate($data->date);
            $purchaseReceipt->is_from_direct_purchase = $data->isFromDirectPurchase;
            $purchaseReceipt->warehouse_id = $data->warehouseId;
            $purchaseReceipt->remarks = $data->remarks;
            $purchaseReceipt->is_posted = $data->isPosted;
            $purchaseReceipt->save();

            foreach ($purchaseReceipt->items as $purchaseReceiptItem) {
                $this->purchaseReceiptItemActions->delete($purchaseReceiptItem, false);
            }

            foreach ($data->items as $item) {
                $dto = new PurchaseReceiptItemCreateDTO(
                    companyId: $purchaseReceipt->company_id,
                    branchId: $purchaseReceipt->branch_id,
                    purchaseReceiptId: $purchaseReceipt->id,
                    purchaseItemId: $item['purchase_item_id'],

                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitQtyBase: $item['product_unit_qty_base'],
                    remarks: $item['remarks'],

                    serials: $item['serials'],
                );

                $this->purchaseReceiptItemActions->create($dto, false);
            }

            self::updateSummary($purchaseReceipt);

            if ($purchaseReceipt->purchase && $updateParent) {
                PurchaseActions::updateSummary($purchaseReceipt->purchase);
            }

            $this->flushCache();

            return $purchaseReceipt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(PurchaseReceipt $purchaseReceipt): void
    {
        $purchaseReceipt->refresh()->load(self::DETAIL_EAGER_LOADS);

        // implament later...
    }

    public function delete(PurchaseReceipt $purchaseReceipt, bool $updateParent): bool
    {
        $timer_start = microtime(true);

        try {
            $purchase = $updateParent ? $purchaseReceipt->purchase : null;

            foreach ($purchaseReceipt->items as $purchaseReceiptItem) {
                $this->purchaseReceiptItemActions->delete($purchaseReceiptItem, false);
            }

            $result = $purchaseReceipt->delete();

            if ($updateParent && $purchase) {
                PurchaseActions::updateSummary($purchase);
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
