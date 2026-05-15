<?php

namespace App\Actions\AssetAdjustmentOutItem;

use App\Actions\AssetAdjustmentOutItemSerial\AssetAdjustmentOutItemSerialActions;
use App\DTOs\AssetAdjustmentOutItemCreateDTO;
use App\DTOs\AssetAdjustmentOutItemSerialCreateDTO;
use App\DTOs\AssetAdjustmentOutItemSerialUpdateDTO;
use App\DTOs\AssetAdjustmentOutItemUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetAdjustmentOutItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetAdjustmentOutItemActions
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
        private readonly AssetAdjustmentOutItemSerialActions $assetAdjustmentOutItemSerialActions,
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
        $query = AssetAdjustmentOutItem::select('asset_adjustment_out_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_adjustment_out_items.company_id')
            ->join('asset_adjustments', 'asset_adjustments.id', '=', 'asset_adjustment_out_items.asset_adjustment_id')
            ->join('assets', 'assets.id', '=', 'asset_adjustment_out_items.asset_id')
            ->whereCompanyId('asset_adjustment_out_items', $companyId)
            ->whereBranchId('asset_adjustment_out_items', $branchId)
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
                        ->orWhere('asset_adjustment_out_items.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($assetAdjustmentId) {
                $query->where('asset_adjustment_out_items.asset_adjustment_id', $assetAdjustmentId);
            }

            if ($assetId) {
                $query->where('asset_adjustment_out_items.asset_id', $assetId);
            }
        });

        $query->orderBy('asset_adjustments.date', 'desc')
            ->orderBy('asset_adjustment_out_items.id', 'asc');

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

            $cacheKey = 'read_any_asset_adjustment_out_item_'.implode('_', $cacheParams);

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

    public function read(AssetAdjustmentOutItem $assetAdjustmentOutItem): AssetAdjustmentOutItem
    {
        return $assetAdjustmentOutItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetAdjustmentOutItemCreateDTO $data): AssetAdjustmentOutItem
    {
        $assetAdjustmentOutItem = new AssetAdjustmentOutItem();
        $assetAdjustmentOutItem->company_id = $data->companyId;
        $assetAdjustmentOutItem->branch_id = $data->branchId;
        $assetAdjustmentOutItem->asset_adjustment_id = $data->assetAdjustmentId;
        $assetAdjustmentOutItem->asset_id = $data->assetId;
        $assetAdjustmentOutItem->qty = $data->qty;
        $assetAdjustmentOutItem->remarks = $data->remarks;
        $assetAdjustmentOutItem->save();

        foreach ($data->serials as $serial) {
            $dto = new AssetAdjustmentOutItemSerialCreateDTO(
                companyId: $assetAdjustmentOutItem->company_id,
                branchId: $assetAdjustmentOutItem->branch_id,
                assetAdjustmentId: $assetAdjustmentOutItem->asset_adjustment_id,
                assetAdjustmentOutItemId: $assetAdjustmentOutItem->id,
                serial: $serial['serial'],
            );
            $this->assetAdjustmentOutItemSerialActions->create($dto);
        }

        $this->flushCache();

        return $assetAdjustmentOutItem;
    }

    public function update(AssetAdjustmentOutItem $assetAdjustmentOutItem, AssetAdjustmentOutItemUpdateDTO $data): AssetAdjustmentOutItem
    {
        $assetAdjustmentOutItem->asset_id = $data->assetId;
        $assetAdjustmentOutItem->qty = $data->qty;
        $assetAdjustmentOutItem->remarks = $data->remarks;
        $assetAdjustmentOutItem->save();

        foreach ($data->deleteSerialIds as $deleteId) {
            $serial = $assetAdjustmentOutItem->serials()->findOrFail($deleteId);
            $this->assetAdjustmentOutItemSerialActions->delete($serial);
        }

        foreach ($data->serials as $serial) {
            if ($serial['id']) {
                $serialRow = $assetAdjustmentOutItem->serials()->findOrFail($serial['id']);
                $dto = new AssetAdjustmentOutItemSerialUpdateDTO(
                    companyId: $assetAdjustmentOutItem->company_id,
                    serial: $serial['serial'],
                );
                $this->assetAdjustmentOutItemSerialActions->update($serialRow, $dto);
            } else {
                $dto = new AssetAdjustmentOutItemSerialCreateDTO(
                    companyId: $assetAdjustmentOutItem->company_id,
                    branchId: $assetAdjustmentOutItem->branch_id,
                    assetAdjustmentId: $assetAdjustmentOutItem->asset_adjustment_id,
                    assetAdjustmentOutItemId: $assetAdjustmentOutItem->id,
                    serial: $serial['serial'],
                );
                $this->assetAdjustmentOutItemSerialActions->create($dto);
            }
        }

        $this->flushCache();

        return $assetAdjustmentOutItem->refresh();
    }

    public function delete(AssetAdjustmentOutItem $assetAdjustmentOutItem): bool
    {
        foreach ($assetAdjustmentOutItem->serials as $serial) {
            $this->assetAdjustmentOutItemSerialActions->delete($serial);
        }

        $result = $assetAdjustmentOutItem->delete();

        $this->flushCache();

        return $result;
    }
}
