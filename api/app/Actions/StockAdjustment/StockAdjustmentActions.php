<?php

namespace App\Actions\StockAdjustment;

use App\Actions\StockAdjustmentInItem\StockAdjustmentInItemActions;
use App\Actions\StockAdjustmentOutItem\StockAdjustmentOutItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentCreateDTO;
use App\DTOs\StockAdjustmentInItemCreateDTO;
use App\DTOs\StockAdjustmentInItemUpdateDTO;
use App\DTOs\StockAdjustmentOutItemCreateDTO;
use App\DTOs\StockAdjustmentOutItemUpdateDTO;
use App\DTOs\StockAdjustmentUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\StockAdjustment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockAdjustmentInItemActions;

    private $stockAdjustmentOutItemActions;

    public function __construct(
        StockAdjustmentInItemActions $stockAdjustmentInItemActions,
        StockAdjustmentOutItemActions $stockAdjustmentOutItemActions,
    ) {
        $this->stockAdjustmentInItemActions = $stockAdjustmentInItemActions;
        $this->stockAdjustmentOutItemActions = $stockAdjustmentOutItemActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?int $categoryId,
        ?int $inWarehouseId,
        ?int $outWarehouseId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustment::select('stock_adjustments.*')
            ->with(['company', 'branch', 'category', 'inWarehouse', 'outWarehouse'])
            ->when($execute?->pagination, function ($query) {
                $query->with([
                    'inItems.productUnit.product.images',
                    'inItems.productUnit.unit',
                    'inItems.serials',
                    'outItems.productUnit.product.images',
                    'outItems.productUnit.unit',
                    'outItems.serials',
                ]);
            })
            ->join('companies', 'companies.id', '=', 'stock_adjustments.company_id')
            ->whereCompanyId('stock_adjustments', $companyId)
            ->whereBranchId('stock_adjustments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $categoryId,
            $inWarehouseId,
            $outWarehouseId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('stock_adjustments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('stock_adjustments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($categoryId) {
                $query->where('stock_adjustments.category_id', $categoryId);
            }

            if ($inWarehouseId) {
                $query->where('stock_adjustments.in_warehouse_id', $inWarehouseId);
            }

            if ($outWarehouseId) {
                $query->where('stock_adjustments.out_warehouse_id', $outWarehouseId);
            }
        });

        $query->orderBy('stock_adjustments.date', 'desc');

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
                    $categoryId ?? '[null]',
                    $inWarehouseId ?? '[null]',
                    $outWarehouseId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_adjustment_'.implode('_', $cacheParams);

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

    public function read(StockAdjustment $stockAdjustment): StockAdjustment
    {
        return $stockAdjustment->load([
            'company',
            'branch',
            'category',
            'inWarehouse',
            'outWarehouse',
            'inItems.productUnit',
            'inItems.productUnit.unit',
            'inItems.productUnit.product.images',
            'inItems.serials',
            'outItems.productUnit',
            'outItems.productUnit.unit',
            'outItems.productUnit.product.images',
            'outItems.serials',
        ]);
    }

    public function create(StockAdjustmentCreateDTO $data): StockAdjustment
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustment = new StockAdjustment();
            $stockAdjustment->company_id = $data->companyId;
            $stockAdjustment->branch_id = $data->branchId;
            $stockAdjustment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $stockAdjustment->date = $this->generateDate($data->date);
            $stockAdjustment->category_id = $data->categoryId;
            $stockAdjustment->in_warehouse_id = $data->inWarehouseId;
            $stockAdjustment->out_warehouse_id = $data->outWarehouseId;
            $stockAdjustment->remarks = $data->remarks;
            $stockAdjustment->is_posted = $data->isPosted;
            $stockAdjustment->save();

            $this->saveInItems($stockAdjustment, $data->inItems);
            $this->saveOutItems($stockAdjustment, $data->outItems);

            $stockAdjustment->total_incoming_item_qty = $stockAdjustment->inItems()->sum('qty');
            $stockAdjustment->total_incoming_item_cogs = $stockAdjustment->inItems()->sum('product_unit_total_cogs');
            $stockAdjustment->total_outgoing_item_qty = $stockAdjustment->outItems()->sum('qty');
            $stockAdjustment->save();

            $this->flushCache();

            return $stockAdjustment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function saveInItems(StockAdjustment $stockAdjustment, array $inItems): void
    {
        foreach ($inItems as $inItem) {
            $data = new StockAdjustmentInItemCreateDTO(
                companyId: $stockAdjustment->company_id,
                branchId: $stockAdjustment->branch_id,
                stockAdjustmentId: $stockAdjustment->id,
                qty: $inItem['qty'],
                productUnitId: $inItem['product_unit_id'],
                productUnitConversionValue: $inItem['product_unit_conversion_value'],
                productUnitCogs: $inItem['product_unit_cogs'],
                remarks: $inItem['remarks'],
                serials: $inItem['serials'],
            );
            $this->stockAdjustmentInItemActions->create($data);
        }
    }

    private function saveOutItems(StockAdjustment $stockAdjustment, array $outItems): void
    {
        foreach ($outItems as $outItem) {
            $data = new StockAdjustmentOutItemCreateDTO(
                companyId: $stockAdjustment->company_id,
                branchId: $stockAdjustment->branch_id,
                stockAdjustmentId: $stockAdjustment->id,
                qty: $outItem['qty'],
                productUnitId: $outItem['product_unit_id'],
                productUnitConversionValue: $outItem['product_unit_conversion_value'],
                remarks: $outItem['remarks'],
                serials: $outItem['serials'],
            );
            $this->stockAdjustmentOutItemActions->create($data);
        }
    }

    public function update(StockAdjustment $stockAdjustment, StockAdjustmentUpdateDTO $data): StockAdjustment
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustment->company_id = $data->companyId;
            $stockAdjustment->branch_id = $data->branchId;
            $stockAdjustment->code = $this->generateUniqueCode($data->companyId, $data->code, $stockAdjustment->id);
            $stockAdjustment->date = $this->generateDate($data->date);
            $stockAdjustment->category_id = $data->categoryId;
            $stockAdjustment->in_warehouse_id = $data->inWarehouseId;
            $stockAdjustment->out_warehouse_id = $data->outWarehouseId;
            $stockAdjustment->remarks = $data->remarks;
            $stockAdjustment->is_posted = $data->isPosted;
            $stockAdjustment->save();

            $this->updateInItems($stockAdjustment, $data->deleteInItemIds, $data->inItems);
            $this->updateOutItems($stockAdjustment, $data->deleteOutItemIds, $data->outItems);

            $stockAdjustment->total_incoming_item_qty = $stockAdjustment->inItems()->sum('qty');
            $stockAdjustment->total_incoming_item_cogs = $stockAdjustment->inItems()->sum('product_unit_total_cogs');
            $stockAdjustment->total_outgoing_item_qty = $stockAdjustment->outItems()->sum('qty');
            $stockAdjustment->save();

            $this->flushCache();

            return $stockAdjustment->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function updateInItems(StockAdjustment $stockAdjustment, array $deleteIds, array $inItems): void
    {
        foreach ($deleteIds as $deleteId) {
            $stockAdjustmentInItem = $stockAdjustment->inItems()->findOrFail($deleteId);
            $this->stockAdjustmentInItemActions->delete($stockAdjustmentInItem);
        }

        foreach ($inItems as $inItem) {
            if ($inItem['id']) {
                $stockAdjustmentInItem = $stockAdjustment->inItems()->findOrFail($inItem['id']);

                $data = new StockAdjustmentInItemUpdateDTO(
                    qty: $inItem['qty'],
                    productUnitId: $inItem['product_unit_id'],
                    productUnitConversionValue: $inItem['product_unit_conversion_value'],
                    productUnitCogs: $inItem['product_unit_cogs'],
                    remarks: $inItem['remarks'],

                    deleteSerialIds: $inItem['delete_serial_ids'],
                    serials: $inItem['serials'],
                );

                $this->stockAdjustmentInItemActions->update($stockAdjustmentInItem, $data);
            } else {
                $data = new StockAdjustmentInItemCreateDTO(
                    companyId: $stockAdjustment->company_id,
                    branchId: $stockAdjustment->branch_id,
                    stockAdjustmentId: $stockAdjustment->id,
                    qty: $inItem['qty'],
                    productUnitId: $inItem['product_unit_id'],
                    productUnitConversionValue: $inItem['product_unit_conversion_value'],
                    productUnitCogs: $inItem['product_unit_cogs'],
                    remarks: $inItem['remarks'],

                    serials: $inItem['serials'],
                );
                $this->stockAdjustmentInItemActions->create($data);
            }
        }
    }

    private function updateOutItems(StockAdjustment $stockAdjustment, array $deleteIds, array $outItems): void
    {
        foreach ($deleteIds as $deleteId) {
            $stockAdjustmentOutItem = $stockAdjustment->outItems()->findOrFail($deleteId);
            $this->stockAdjustmentOutItemActions->delete($stockAdjustmentOutItem);
        }

        foreach ($outItems as $outItem) {
            if ($outItem['id']) {
                $stockAdjustmentOutItem = $stockAdjustment->outItems()->findOrFail($outItem['id']);

                $data = new StockAdjustmentOutItemUpdateDTO(
                    qty: $outItem['qty'],
                    productUnitId: $outItem['product_unit_id'],
                    productUnitConversionValue: $outItem['product_unit_conversion_value'],
                    remarks: $outItem['remarks'],

                    deleteSerialIds: $outItem['delete_serial_ids'],
                    serials: $outItem['serials'],
                );

                $this->stockAdjustmentOutItemActions->update($stockAdjustmentOutItem, $data);
            } else {
                $data = new StockAdjustmentOutItemCreateDTO(
                    companyId: $stockAdjustment->company_id,
                    branchId: $stockAdjustment->branch_id,
                    stockAdjustmentId: $stockAdjustment->id,
                    qty: $outItem['qty'],
                    productUnitId: $outItem['product_unit_id'],
                    productUnitConversionValue: $outItem['product_unit_conversion_value'],
                    remarks: $outItem['remarks'],

                    serials: $outItem['serials'],
                );
                $this->stockAdjustmentOutItemActions->create($data);
            }
        }
    }

    public function delete(StockAdjustment $stockAdjustment): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $stockAdjustment->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->stockAdjustments()->withTrashed()->count() + 1 + $tryCount;
                $code = 'SA'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = StockAdjustment::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
