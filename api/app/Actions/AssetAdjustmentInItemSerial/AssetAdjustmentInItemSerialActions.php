<?php

namespace App\Actions\AssetAdjustmentInItemSerial;

use App\DTOs\AssetAdjustmentInItemSerialCreateDTO;
use App\DTOs\AssetAdjustmentInItemSerialUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetAdjustmentInItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetAdjustmentInItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'assetAdjustment',
        'assetAdjustmentInItem.asset.assetCategory',
        'assetAdjustmentInItem.asset.assetUnit',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $assetAdjustmentId,
        ?int $assetAdjustmentInItemId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetAdjustmentInItemSerial::select('asset_adjustment_in_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_adjustment_in_item_serials.company_id')
            ->join('asset_adjustments', 'asset_adjustments.id', '=', 'asset_adjustment_in_item_serials.asset_adjustment_id')
            ->join('asset_adjustment_in_items', 'asset_adjustment_in_items.id', '=', 'asset_adjustment_in_item_serials.asset_adjustment_in_item_id')
            ->join('assets', 'assets.id', '=', 'asset_adjustment_in_items.asset_id')
            ->whereCompanyId('asset_adjustment_in_item_serials', $companyId)
            ->whereBranchId('asset_adjustment_in_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetAdjustmentId,
            $assetAdjustmentInItemId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_adjustment_in_item_serials.serial', 'like', '%'.$search.'%')
                        ->orWhere('asset_adjustments.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.name', 'like', '%'.$search.'%');
                });
            }

            if ($assetAdjustmentId) {
                $query->where('asset_adjustment_in_item_serials.asset_adjustment_id', $assetAdjustmentId);
            }

            if ($assetAdjustmentInItemId) {
                $query->where('asset_adjustment_in_item_serials.asset_adjustment_in_item_id', $assetAdjustmentInItemId);
            }
        });

        $query->orderBy('asset_adjustments.date', 'desc')
            ->orderBy('asset_adjustment_in_item_serials.id', 'asc');

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
                $assetAdjustmentId ?? '[null]',
                $assetAdjustmentInItemId ?? '[null]',
                $execute->pagination ? 'true' : 'false',
                $execute->pagination?->page ?? '[null]',
                $execute->pagination?->perPage ?? '[null]',
                $execute->get?->limit ?? '[null]',
            ];

            $cacheKey = 'read_any_asset_adjustment_in_item_serial_'.implode('_', $cacheParams);

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

    public function read(AssetAdjustmentInItemSerial $assetAdjustmentInItemSerial): AssetAdjustmentInItemSerial
    {
        return $assetAdjustmentInItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetAdjustmentInItemSerialCreateDTO $data): AssetAdjustmentInItemSerial
    {
        $timer_start = microtime(true);

        try {
            $assetAdjustmentInItemSerial = new AssetAdjustmentInItemSerial();
            $assetAdjustmentInItemSerial->company_id = $data->companyId;
            $assetAdjustmentInItemSerial->branch_id = $data->branchId;
            $assetAdjustmentInItemSerial->asset_adjustment_id = $data->assetAdjustmentId;
            $assetAdjustmentInItemSerial->asset_adjustment_in_item_id = $data->assetAdjustmentInItemId;
            $assetAdjustmentInItemSerial->serial = $data->serial;
            $assetAdjustmentInItemSerial->save();

            $this->flushCache();

            return $assetAdjustmentInItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        AssetAdjustmentInItemSerial $assetAdjustmentInItemSerial,
        AssetAdjustmentInItemSerialUpdateDTO $data
    ): AssetAdjustmentInItemSerial {
        $timer_start = microtime(true);

        try {
            $assetAdjustmentInItemSerial->serial = $data->serial;
            $assetAdjustmentInItemSerial->save();

            $this->flushCache();

            return $assetAdjustmentInItemSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(AssetAdjustmentInItemSerial $assetAdjustmentInItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $assetAdjustmentInItemSerial->delete();

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
