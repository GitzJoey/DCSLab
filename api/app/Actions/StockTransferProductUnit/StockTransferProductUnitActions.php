<?php

namespace App\Actions\StockTransferProductUnit;

use App\Actions\StockTransaction\StockTransactionActions;
use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\DTOs\StockTransferProductUnitCreateDTO;
use App\DTOs\StockTransferProductUnitSerialCreateDTO;
use App\DTOs\StockTransferProductUnitSerialUpdateDTO;
use App\DTOs\StockTransferProductUnitUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\StockTransferProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StockTransferProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockTransferProductUnitSerialActions;

    private $stockTransactionActions;

    public function __construct(
        StockTransferProductUnitSerialActions $stockTransferProductUnitSerialActions,
        StockTransactionActions $stockTransactionActions,
    ) {
        $this->stockTransferProductUnitSerialActions = $stockTransferProductUnitSerialActions;
        $this->stockTransactionActions = $stockTransactionActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $stockTransferCode,
        ?string $stockTransferStartDate,
        ?string $stockTransferEndDate,
        ?int $stockTransferSourceWarehouseId,
        ?int $stockTransferDestinationWarehouseId,
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockTransferProductUnit::select('stock_transfer_product_units.*')
            ->with([
                'company',
                'branch',
                'stockTransfer.sourceWarehouse',
                'stockTransfer.destinationWarehouse',
                'productUnit.unit',
                'productUnit.product.category',
                'productUnit.product.brand',
                'productUnit.product.baseProductUnit.unit',
                'productUnit.product.images',
                'serials',
            ])
            ->join('companies', 'companies.id', '=', 'stock_transfer_product_units.company_id')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_product_units.stock_transfer_id')
            ->join('product_units', 'product_units.id', '=', 'stock_transfer_product_units.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_transfer_product_units', $companyId)
            ->whereBranchId('stock_transfer_product_units', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $stockTransferCode,
            $stockTransferStartDate,
            $stockTransferEndDate,
            $stockTransferSourceWarehouseId,
            $stockTransferDestinationWarehouseId,
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

            $stockTransferStartDateUtc = $stockTransferStartDate ? TimezoneHelper::convertToUTC($stockTransferStartDate) : null;
            if ($stockTransferStartDateUtc) {
                $query->where('stock_transfers.date', '>=', $stockTransferStartDateUtc);
            }

            $stockTransferEndDateUtc = $stockTransferEndDate ? TimezoneHelper::convertToUTC($stockTransferEndDate) : null;
            if ($stockTransferEndDateUtc) {
                $query->where('stock_transfers.date', '<=', $stockTransferEndDateUtc);
            }

            if ($stockTransferCode) {
                $query->where('stock_transfers.code', $stockTransferCode);
            }

            if ($stockTransferSourceWarehouseId) {
                $query->where('stock_transfers.source_warehouse_id', $stockTransferSourceWarehouseId);
            }

            if ($stockTransferDestinationWarehouseId) {
                $query->where('stock_transfers.destination_warehouse_id', $stockTransferDestinationWarehouseId);
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

        $query->orderBy('stock_transfers.date', 'desc')
            ->orderBy('stock_transfer_product_units.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($stockTransferCode) ? '[empty]' : $stockTransferCode,
                    $stockTransferStartDate ?? '[null]',
                    $stockTransferEndDate ?? '[null]',
                    $stockTransferSourceWarehouseId ?? '[null]',
                    $stockTransferDestinationWarehouseId ?? '[null]',
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];
                $cacheKey = 'read_any_stock_transfer_product_unit_'.implode('_', $cacheParams);

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

    public function read(StockTransferProductUnit $stockTransferProductUnit): StockTransferProductUnit
    {
        return $stockTransferProductUnit->load([
            'company',
            'branch',
            'stockTransfer',
            'productUnit.unit',
            'productUnit.product.category',
            'productUnit.product.brand',
            'productUnit.product.images',
            'serials',
        ]);
    }

    public function create(StockTransferProductUnitCreateDTO $data): StockTransferProductUnit
    {
        $timer_start = microtime(true);

        try {
            $stockTransferProductUnit = new StockTransferProductUnit();
            $stockTransferProductUnit->company_id = $data->companyId;
            $stockTransferProductUnit->branch_id = $data->branchId;
            $stockTransferProductUnit->stock_transfer_id = $data->stockTransferId;
            $stockTransferProductUnit->qty = $data->qty;
            $stockTransferProductUnit->product_unit_id = $data->productUnitId;
            $stockTransferProductUnit->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockTransferProductUnit->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockTransferProductUnit->remarks = $data->remarks;
            $stockTransferProductUnit->save();

            foreach ($data->serials as $serial) {
                $dto = StockTransferProductUnitSerialCreateDTO::fromStockTransferProductUnit($stockTransferProductUnit, $serial['serial']);
                $this->stockTransferProductUnitSerialActions->create($dto);
            }

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockTransferProductUnitSource($stockTransferProductUnit)
            );
            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockTransferProductUnitDestination($stockTransferProductUnit)
            );

            $this->flushCache();

            return $stockTransferProductUnit;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockTransferProductUnit $stockTransferProductUnit, StockTransferProductUnitUpdateDTO $data): StockTransferProductUnit
    {
        $timer_start = microtime(true);

        try {
            $stockTransferProductUnit->qty = $data->qty;
            $stockTransferProductUnit->product_unit_id = $data->productUnitId;
            $stockTransferProductUnit->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockTransferProductUnit->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockTransferProductUnit->remarks = $data->remarks;
            $stockTransferProductUnit->save();

            foreach ($data->deleteSerialIds as $deleteId) {
                $stockTransferProductUnitSerial = $stockTransferProductUnit->serials()->findOrFail($deleteId);
                $this->stockTransferProductUnitSerialActions->delete($stockTransferProductUnitSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $stockTransferProductUnitSerial = $stockTransferProductUnit->serials()->findOrFail($serial['id']);
                    $dto = StockTransferProductUnitSerialUpdateDTO::fromStockTransferProductUnitSerial($stockTransferProductUnitSerial, $serial['serial']);
                    $this->stockTransferProductUnitSerialActions->update($stockTransferProductUnitSerial, $dto);
                } else {
                    $dto = StockTransferProductUnitSerialCreateDTO::fromStockTransferProductUnit($stockTransferProductUnit, $serial['serial']);
                    $this->stockTransferProductUnitSerialActions->create($dto);
                }
            }

            $stockTransactionSource = $stockTransferProductUnit->sourceStockTransaction;
            if (! $stockTransactionSource) {
                $dto = StockTransactionCreateDTO::fromStockTransferProductUnitSource($stockTransferProductUnit);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockTransferProductUnitSource($stockTransferProductUnit);
                $this->stockTransactionActions->update($stockTransactionSource, $dto);
            }

            $stockTransactionDestination = $stockTransferProductUnit->destinationStockTransaction;
            if (! $stockTransactionDestination) {
                $dto = StockTransactionCreateDTO::fromStockTransferProductUnitDestination($stockTransferProductUnit);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockTransferProductUnitDestination($stockTransferProductUnit);
                $this->stockTransactionActions->update($stockTransactionDestination, $dto);
            }

            $this->flushCache();

            return $stockTransferProductUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockTransferProductUnit $stockTransferProductUnit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockTransferProductUnitSerials = $stockTransferProductUnit->serials()->get();
            foreach ($stockTransferProductUnitSerials as $stockTransferProductUnitSerial) {
                $this->stockTransferProductUnitSerialActions->delete($stockTransferProductUnitSerial);
            }

            $stockTransactionSource = $stockTransferProductUnit->sourceStockTransaction;
            if ($stockTransactionSource) {
                $this->stockTransactionActions->delete($stockTransactionSource);
            }

            $stockTransactionDestination = $stockTransferProductUnit->destinationStockTransaction;
            if ($stockTransactionDestination) {
                $this->stockTransactionActions->delete($stockTransactionDestination);
            }

            $retval = $stockTransferProductUnit->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->stockTransferProductUnits()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = StockTransferProductUnit::whereCompanyId('stock_transfer_product_units', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
