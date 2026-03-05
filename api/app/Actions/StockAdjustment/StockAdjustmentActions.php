<?php

namespace App\Actions\StockAdjustment;

use App\Actions\StockAdjustmentInProduct\StockAdjustmentInProductActions;
use App\Actions\StockAdjustmentOutProduct\StockAdjustmentOutProductActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentCreateDTO;
use App\DTOs\StockAdjustmentInProductCreateDTO;
use App\DTOs\StockAdjustmentInProductUpdateDTO;
use App\DTOs\StockAdjustmentOutProductCreateDTO;
use App\DTOs\StockAdjustmentOutProductUpdateDTO;
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

    private $stockAdjustmentInProductActions;

    private $stockAdjustmentOutProductActions;

    public function __construct(
        StockAdjustmentInProductActions $stockAdjustmentInProductActions,
        StockAdjustmentOutProductActions $stockAdjustmentOutProductActions,
    ) {
        $this->stockAdjustmentInProductActions = $stockAdjustmentInProductActions;
        $this->stockAdjustmentOutProductActions = $stockAdjustmentOutProductActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustment::select('stock_adjustments.*')
            ->with(['company', 'branch', 'category', 'inWarehouse', 'outWarehouse'])
            ->when($execute?->pagination, function ($query) {
                $query->with([
                    'inProducts.productUnit.product',
                    'inProducts.productUnit.unit',
                    'outProducts.productUnit.product',
                    'outProducts.productUnit.unit',
                ]);
            })
            ->join('companies', 'companies.id', '=', 'stock_adjustments.company_id')
            ->whereCompanyId('stock_adjustments', $companyId)
            ->whereBranchId($branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
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
            'inProducts.productUnit.product',
            'outProducts.productUnit.product',
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

            $this->saveInProducts($stockAdjustment, $data->inProducts);
            $this->saveOutProducts($stockAdjustment, $data->outProducts);

            $stockAdjustment->total_incoming_product_qty = $stockAdjustment->inProducts()->sum('qty');
            $stockAdjustment->total_incoming_product_cogs = $stockAdjustment->inProducts()->sum('product_unit_total_cogs');
            $stockAdjustment->total_outgoing_product_qty = $stockAdjustment->outProducts()->sum('qty');
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

    private function saveInProducts(StockAdjustment $stockAdjustment, array $inProducts): void
    {
        foreach ($inProducts as $inProduct) {
            $data = new StockAdjustmentInProductCreateDTO(
                companyId: $stockAdjustment->company_id,
                branchId: $stockAdjustment->branch_id,
                stockAdjustmentId: $stockAdjustment->id,
                qty: $inProduct['qty'],
                productUnitId: $inProduct['product_unit_id'],
                productUnitConversionValue: $inProduct['product_unit_conversion_value'],
                productUnitCogs: $inProduct['product_unit_cogs'],
                remarks: $inProduct['remarks'],
            );

            $this->stockAdjustmentInProductActions->create($data);
        }
    }

    private function saveOutProducts(StockAdjustment $stockAdjustment, array $outProducts): void
    {
        foreach ($outProducts as $outProduct) {
            $data = new StockAdjustmentOutProductCreateDTO(
                companyId: $stockAdjustment->company_id,
                branchId: $stockAdjustment->branch_id,
                stockAdjustmentId: $stockAdjustment->id,
                qty: $outProduct['qty'],
                productUnitId: $outProduct['product_unit_id'],
                productUnitConversionValue: $outProduct['product_unit_conversion_value'],
                remarks: $outProduct['remarks'],
            );

            $this->stockAdjustmentOutProductActions->create($data);
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

            $this->updateInProducts($stockAdjustment, $data->deleteInProductIds, $data->inProducts);
            $this->updateOutProducts($stockAdjustment, $data->deleteOutProductIds, $data->outProducts);

            $stockAdjustment->total_incoming_product_qty = $stockAdjustment->inProducts()->sum('qty');
            $stockAdjustment->total_incoming_product_cogs = $stockAdjustment->inProducts()->sum('product_unit_total_cogs');
            $stockAdjustment->total_outgoing_product_qty = $stockAdjustment->outProducts()->sum('qty');
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

    private function updateInProducts(StockAdjustment $stockAdjustment, array $deleteIds, array $inProducts): void
    {
        foreach ($deleteIds as $deleteId) {
            $stockAdjustmentInProduct = $stockAdjustment->inProducts()->findOrFail($deleteId);
            $this->stockAdjustmentInProductActions->delete($stockAdjustmentInProduct);
        }

        foreach ($inProducts as $inProduct) {
            if ($inProduct['id']) {
                $stockAdjustmentInProduct = $stockAdjustment->inProducts()->findOrFail($inProduct['id']);

                $data = new StockAdjustmentInProductUpdateDTO(
                    qty: $inProduct['qty'],
                    productUnitId: $inProduct['product_unit_id'],
                    productUnitConversionValue: $inProduct['product_unit_conversion_value'],
                    productUnitCogs: $inProduct['product_unit_cogs'],
                    remarks: $inProduct['remarks'],
                );

                $this->stockAdjustmentInProductActions->update($stockAdjustmentInProduct, $data);
            } else {
                $data = new StockAdjustmentInProductCreateDTO(
                    companyId: $stockAdjustment->company_id,
                    branchId: $stockAdjustment->branch_id,
                    stockAdjustmentId: $stockAdjustment->id,
                    qty: $inProduct['qty'],
                    productUnitId: $inProduct['product_unit_id'],
                    productUnitConversionValue: $inProduct['product_unit_conversion_value'],
                    productUnitCogs: $inProduct['product_unit_cogs'],
                    remarks: $inProduct['remarks'],
                );

                $this->stockAdjustmentInProductActions->create($data);
            }
        }
    }

    private function updateOutProducts(StockAdjustment $stockAdjustment, array $deleteIds, array $outProducts): void
    {
        foreach ($deleteIds as $deleteId) {
            $stockAdjustmentOutProduct = $stockAdjustment->outProducts()->findOrFail($deleteId);
            $this->stockAdjustmentOutProductActions->delete($stockAdjustmentOutProduct);
        }

        foreach ($outProducts as $outProduct) {
            if ($outProduct['id']) {
                $stockAdjustmentOutProduct = $stockAdjustment->outProducts()->findOrFail($outProduct['id']);

                $data = new StockAdjustmentOutProductUpdateDTO(
                    qty: $outProduct['qty'],
                    productUnitId: $outProduct['product_unit_id'],
                    productUnitConversionValue: $outProduct['product_unit_conversion_value'],
                    remarks: $outProduct['remarks'],
                );

                $this->stockAdjustmentOutProductActions->update($stockAdjustmentOutProduct, $data);
            } else {
                $data = new StockAdjustmentOutProductCreateDTO(
                    companyId: $stockAdjustment->company_id,
                    branchId: $stockAdjustment->branch_id,
                    stockAdjustmentId: $stockAdjustment->id,
                    qty: $outProduct['qty'],
                    productUnitId: $outProduct['product_unit_id'],
                    productUnitConversionValue: $outProduct['product_unit_conversion_value'],
                    remarks: $outProduct['remarks'],
                );

                $this->stockAdjustmentOutProductActions->create($data);
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
