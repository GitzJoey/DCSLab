<?php

namespace App\Actions\AssetPurchaseItemSerial;

use App\DTOs\AssetPurchaseItemSerialCreateDTO;
use App\DTOs\AssetPurchaseItemSerialUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetPurchaseItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetPurchaseItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'assetPurchase.supplier',
        'assetPurchaseItem.asset.assetCategory',
        'assetPurchaseItem.asset.assetUnit',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $assetPurchaseId,
        ?int $assetPurchaseItemId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetPurchaseItemSerial::select('asset_purchase_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_purchase_item_serials.company_id')
            ->join('asset_purchases', 'asset_purchases.id', '=', 'asset_purchase_item_serials.asset_purchase_id')
            ->join('asset_purchase_items', 'asset_purchase_items.id', '=', 'asset_purchase_item_serials.asset_purchase_item_id')
            ->join('assets', 'assets.id', '=', 'asset_purchase_items.asset_id')
            ->whereCompanyId('asset_purchase_item_serials', $companyId)
            ->whereBranchId('asset_purchase_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetPurchaseId,
            $assetPurchaseItemId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_purchase_item_serials.serial', 'like', '%'.$search.'%')
                        ->orWhere('asset_purchases.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.name', 'like', '%'.$search.'%');
                });
            }

            if ($assetPurchaseId) {
                $query->where('asset_purchase_item_serials.asset_purchase_id', $assetPurchaseId);
            }

            if ($assetPurchaseItemId) {
                $query->where('asset_purchase_item_serials.asset_purchase_item_id', $assetPurchaseItemId);
            }
        });

        $query->orderBy('asset_purchases.date', 'desc')
            ->orderBy('asset_purchase_item_serials.id', 'asc');

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
                $assetPurchaseId ?? '[null]',
                $assetPurchaseItemId ?? '[null]',
                $execute->pagination ? 'true' : 'false',
                $execute->pagination?->page ?? '[null]',
                $execute->pagination?->perPage ?? '[null]',
                $execute->get?->limit ?? '[null]',
            ];

            $cacheKey = 'read_any_asset_purchase_item_serial_'.implode('_', $cacheParams);

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

    public function read(AssetPurchaseItemSerial $assetPurchaseItemSerial): AssetPurchaseItemSerial
    {
        return $assetPurchaseItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetPurchaseItemSerialCreateDTO $data): AssetPurchaseItemSerial
    {
        $timer_start = microtime(true);

        try {
            $assetPurchaseItemSerial = new AssetPurchaseItemSerial();
            $assetPurchaseItemSerial->company_id = $data->companyId;
            $assetPurchaseItemSerial->branch_id = $data->branchId;
            $assetPurchaseItemSerial->asset_purchase_id = $data->assetPurchaseId;
            $assetPurchaseItemSerial->asset_purchase_item_id = $data->assetPurchaseItemId;
            $assetPurchaseItemSerial->serial = $data->serial;
            $assetPurchaseItemSerial->save();

            $this->flushCache();

            return $assetPurchaseItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        AssetPurchaseItemSerial $assetPurchaseItemSerial,
        AssetPurchaseItemSerialUpdateDTO $data
    ): AssetPurchaseItemSerial {
        $timer_start = microtime(true);

        try {
            $assetPurchaseItemSerial->serial = $data->serial;
            $assetPurchaseItemSerial->save();

            $this->flushCache();

            return $assetPurchaseItemSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(AssetPurchaseItemSerial $assetPurchaseItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $result = $assetPurchaseItemSerial->delete();

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
