<?php

namespace App\Actions\AssetSaleItemSerial;

use App\DTOs\AssetSaleItemSerialCreateDTO;
use App\DTOs\AssetSaleItemSerialUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetSaleItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetSaleItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'assetSale.customer',
        'assetSaleItem.asset.assetCategory',
        'assetSaleItem.asset.assetUnit',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $assetSaleId,
        ?int $assetSaleItemId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetSaleItemSerial::select('asset_sale_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_sale_item_serials.company_id')
            ->join('asset_sales', 'asset_sales.id', '=', 'asset_sale_item_serials.asset_sale_id')
            ->join('asset_sale_items', 'asset_sale_items.id', '=', 'asset_sale_item_serials.asset_sale_item_id')
            ->join('assets', 'assets.id', '=', 'asset_sale_items.asset_id')
            ->whereCompanyId('asset_sale_item_serials', $companyId)
            ->whereBranchId('asset_sale_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetSaleId,
            $assetSaleItemId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_sale_item_serials.serial', 'like', '%'.$search.'%')
                        ->orWhere('asset_sales.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.name', 'like', '%'.$search.'%');
                });
            }

            if ($assetSaleId) {
                $query->where('asset_sale_item_serials.asset_sale_id', $assetSaleId);
            }

            if ($assetSaleItemId) {
                $query->where('asset_sale_item_serials.asset_sale_item_id', $assetSaleItemId);
            }
        });

        $query->orderBy('asset_sales.date', 'desc')
            ->orderBy('asset_sale_item_serials.id', 'asc');

        if (! $execute) {
            return $query;
        }

        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheParams = [
                $withTrashed ? 'true' : 'false',
                $companyId,
                $branchId ?? '[null]',
                empty($search) ? '[empty]' : $search,
                $assetSaleId ?? '[null]',
                $assetSaleItemId ?? '[null]',
                $execute->pagination ? 'true' : 'false',
                $execute->pagination?->page ?? '[null]',
                $execute->pagination?->perPage ?? '[null]',
                $execute->get?->limit ?? '[null]',
            ];

            $cacheKey = 'read_any_asset_sale_item_serial_'.implode('_', $cacheParams);

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

    public function read(AssetSaleItemSerial $assetSaleItemSerial): AssetSaleItemSerial
    {
        return $assetSaleItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetSaleItemSerialCreateDTO $data): AssetSaleItemSerial
    {
        $timer_start = microtime(true);

        try {
            $assetSaleItemSerial = new AssetSaleItemSerial();
            $assetSaleItemSerial->company_id = $data->companyId;
            $assetSaleItemSerial->branch_id = $data->branchId;
            $assetSaleItemSerial->asset_sale_id = $data->assetSaleId;
            $assetSaleItemSerial->asset_sale_item_id = $data->assetSaleItemId;
            $assetSaleItemSerial->serial = $data->serial;
            $assetSaleItemSerial->save();

            $this->flushCache();

            return $assetSaleItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        AssetSaleItemSerial $assetSaleItemSerial,
        AssetSaleItemSerialUpdateDTO $data
    ): AssetSaleItemSerial {
        $timer_start = microtime(true);

        try {
            $assetSaleItemSerial->serial = $data->serial;
            $assetSaleItemSerial->save();

            $this->flushCache();

            return $assetSaleItemSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(AssetSaleItemSerial $assetSaleItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $assetSaleItemSerial->delete();

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
