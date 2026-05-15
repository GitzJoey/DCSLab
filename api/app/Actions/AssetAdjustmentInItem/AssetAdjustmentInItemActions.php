<?php

namespace App\Actions\AssetAdjustmentInItem;

use App\Actions\AssetAdjustmentInItemSerial\AssetAdjustmentInItemSerialActions;
use App\DTOs\AssetAdjustmentInItemCreateDTO;
use App\DTOs\AssetAdjustmentInItemSerialCreateDTO;
use App\DTOs\AssetAdjustmentInItemSerialUpdateDTO;
use App\DTOs\AssetAdjustmentInItemUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetAdjustmentInItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetAdjustmentInItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'assetAdjustment',
        'asset.assetCategory',
        'asset.assetUnit',
        'serials',
    ];

    public function __construct(
        private readonly AssetAdjustmentInItemSerialActions $assetAdjustmentInItemSerialActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $assetAdjustmentId,
        ?int $assetId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetAdjustmentInItem::select('asset_adjustment_in_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_adjustment_in_items.company_id')
            ->join('asset_adjustments', 'asset_adjustments.id', '=', 'asset_adjustment_in_items.asset_adjustment_id')
            ->join('assets', 'assets.id', '=', 'asset_adjustment_in_items.asset_id')
            ->whereCompanyId('asset_adjustment_in_items', $companyId)
            ->whereBranchId('asset_adjustment_in_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetAdjustmentId,
            $assetId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_adjustments.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.name', 'like', '%'.$search.'%')
                        ->orWhere('asset_adjustment_in_items.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($assetAdjustmentId) {
                $query->where('asset_adjustment_in_items.asset_adjustment_id', $assetAdjustmentId);
            }

            if ($assetId) {
                $query->where('asset_adjustment_in_items.asset_id', $assetId);
            }
        });

        $query->orderBy('asset_adjustments.date', 'desc')
            ->orderBy('asset_adjustment_in_items.id', 'asc');

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
                $assetId ?? '[null]',
                $execute->pagination ? 'true' : 'false',
                $execute->pagination?->page ?? '[null]',
                $execute->pagination?->perPage ?? '[null]',
                $execute->get?->limit ?? '[null]',
            ];

            $cacheKey = 'read_any_asset_adjustment_in_item_'.implode('_', $cacheParams);

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

    public function read(AssetAdjustmentInItem $assetAdjustmentInItem): AssetAdjustmentInItem
    {
        return $assetAdjustmentInItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetAdjustmentInItemCreateDTO $data): AssetAdjustmentInItem
    {
        $assetAdjustmentInItem = new AssetAdjustmentInItem();
        $assetAdjustmentInItem->company_id = $data->companyId;
        $assetAdjustmentInItem->branch_id = $data->branchId;
        $assetAdjustmentInItem->asset_adjustment_id = $data->assetAdjustmentId;
        $assetAdjustmentInItem->asset_id = $data->assetId;
        $assetAdjustmentInItem->qty = $data->qty;
        $assetAdjustmentInItem->remarks = $data->remarks;
        $assetAdjustmentInItem->save();

        foreach ($data->serials as $serial) {
            $dto = new AssetAdjustmentInItemSerialCreateDTO(
                companyId: $assetAdjustmentInItem->company_id,
                branchId: $assetAdjustmentInItem->branch_id,
                assetAdjustmentId: $assetAdjustmentInItem->asset_adjustment_id,
                assetAdjustmentInItemId: $assetAdjustmentInItem->id,
                serial: $serial['serial'],
            );
            $this->assetAdjustmentInItemSerialActions->create($dto);
        }

        $this->flushCache();

        return $assetAdjustmentInItem;
    }

    public function update(AssetAdjustmentInItem $assetAdjustmentInItem, AssetAdjustmentInItemUpdateDTO $data): AssetAdjustmentInItem
    {
        $assetAdjustmentInItem->asset_id = $data->assetId;
        $assetAdjustmentInItem->qty = $data->qty;
        $assetAdjustmentInItem->remarks = $data->remarks;
        $assetAdjustmentInItem->save();

        foreach ($data->deleteSerialIds as $deleteId) {
            $serial = $assetAdjustmentInItem->serials()->findOrFail($deleteId);
            $this->assetAdjustmentInItemSerialActions->delete($serial);
        }

        foreach ($data->serials as $serial) {
            if ($serial['id']) {
                $serialRow = $assetAdjustmentInItem->serials()->findOrFail($serial['id']);
                $dto = new AssetAdjustmentInItemSerialUpdateDTO(
                    serial: $serial['serial'],
                );
                $this->assetAdjustmentInItemSerialActions->update($serialRow, $dto);
            } else {
                $dto = new AssetAdjustmentInItemSerialCreateDTO(
                    companyId: $assetAdjustmentInItem->company_id,
                    branchId: $assetAdjustmentInItem->branch_id,
                    assetAdjustmentId: $assetAdjustmentInItem->asset_adjustment_id,
                    assetAdjustmentInItemId: $assetAdjustmentInItem->id,
                    serial: $serial['serial'],
                );
                $this->assetAdjustmentInItemSerialActions->create($dto);
            }
        }

        $this->flushCache();

        return $assetAdjustmentInItem->refresh();
    }

    public function delete(AssetAdjustmentInItem $assetAdjustmentInItem): bool
    {
        foreach ($assetAdjustmentInItem->serials as $serial) {
            $this->assetAdjustmentInItemSerialActions->delete($serial);
        }

        $result = $assetAdjustmentInItem->delete();

        $this->flushCache();

        return $result;
    }
}
