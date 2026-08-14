<?php

namespace App\Actions\AssetPurchaseItem;

use App\Actions\AssetPurchaseItemSerial\AssetPurchaseItemSerialActions;
use App\DTOs\AssetPurchaseItemCreateDTO;
use App\DTOs\AssetPurchaseItemSerialCreateDTO;
use App\DTOs\AssetPurchaseItemSerialUpdateDTO;
use App\DTOs\AssetPurchaseItemUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetPurchaseItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetPurchaseItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'assetPurchase.supplier',
        'asset.assetCategory',
        'asset.assetUnit',
        'serials',
    ];

    public function __construct(
        private readonly AssetPurchaseItemSerialActions $assetPurchaseItemSerialActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $assetPurchaseId,
        ?int $assetId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetPurchaseItem::select('asset_purchase_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_purchase_items.company_id')
            ->join('asset_purchases', 'asset_purchases.id', '=', 'asset_purchase_items.asset_purchase_id')
            ->join('assets', 'assets.id', '=', 'asset_purchase_items.asset_id')
            ->whereCompanyId('asset_purchase_items', $companyId)
            ->whereBranchId('asset_purchase_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetPurchaseId,
            $assetId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_purchases.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.name', 'like', '%'.$search.'%')
                        ->orWhere('asset_purchase_items.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($assetPurchaseId) {
                $query->where('asset_purchase_items.asset_purchase_id', $assetPurchaseId);
            }

            if ($assetId) {
                $query->where('asset_purchase_items.asset_id', $assetId);
            }
        });

        $query->orderBy('asset_purchases.date', 'desc')
            ->orderBy('asset_purchase_items.id', 'asc');

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
                $assetId ?? '[null]',
                $execute->pagination ? 'true' : 'false',
                $execute->pagination?->page ?? '[null]',
                $execute->pagination?->perPage ?? '[null]',
                $execute->get?->limit ?? '[null]',
            ];

            $cacheKey = 'read_any_asset_purchase_item_'.implode('_', $cacheParams);

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

    public function read(AssetPurchaseItem $assetPurchaseItem): AssetPurchaseItem
    {
        return $assetPurchaseItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetPurchaseItemCreateDTO $data): AssetPurchaseItem
    {
        $assetPurchaseItem = new AssetPurchaseItem();
        $assetPurchaseItem->company_id = $data->companyId;
        $assetPurchaseItem->branch_id = $data->branchId;
        $assetPurchaseItem->asset_purchase_id = $data->assetPurchaseId;
        $assetPurchaseItem->asset_id = $data->assetId;
        $assetPurchaseItem->qty = $data->qty;
        $assetPurchaseItem->unit_price = $data->unitPrice;
        $assetPurchaseItem->subtotal = number_format(round((float) $data->qty * (float) $data->unitPrice, 8), 8, '.', '');
        $assetPurchaseItem->allocated_additional_cost = 0;
        $assetPurchaseItem->subtotal_after_additional_cost = 0;
        $assetPurchaseItem->unit_acquisition_cost = 0;
        $assetPurchaseItem->remarks = $data->remarks;
        $assetPurchaseItem->save();

        foreach ($data->serials as $serial) {
            $dto = new AssetPurchaseItemSerialCreateDTO(
                companyId: $assetPurchaseItem->company_id,
                branchId: $assetPurchaseItem->branch_id,
                assetPurchaseId: $assetPurchaseItem->asset_purchase_id,
                assetPurchaseItemId: $assetPurchaseItem->id,
                serial: $serial['serial'],
            );
            $this->assetPurchaseItemSerialActions->create($dto);
        }

        $this->flushCache();

        return $assetPurchaseItem;
    }

    public function update(AssetPurchaseItem $assetPurchaseItem, AssetPurchaseItemUpdateDTO $data): AssetPurchaseItem
    {
        $assetPurchaseItem->asset_id = $data->assetId;
        $assetPurchaseItem->qty = $data->qty;
        $assetPurchaseItem->unit_price = $data->unitPrice;
        $assetPurchaseItem->subtotal = number_format(round((float) $data->qty * (float) $data->unitPrice, 8), 8, '.', '');
        $assetPurchaseItem->remarks = $data->remarks;
        $assetPurchaseItem->save();

        foreach ($data->deleteSerialIds as $deleteId) {
            $serial = $assetPurchaseItem->serials()->findOrFail($deleteId);
            $this->assetPurchaseItemSerialActions->delete($serial);
        }

        foreach ($data->serials as $serial) {
            if ($serial['id']) {
                $serialRow = $assetPurchaseItem->serials()->findOrFail($serial['id']);
                $dto = new AssetPurchaseItemSerialUpdateDTO(
                    companyId: $assetPurchaseItem->company_id,
                    serial: $serial['serial'],
                );
                $this->assetPurchaseItemSerialActions->update($serialRow, $dto);
            } else {
                $dto = new AssetPurchaseItemSerialCreateDTO(
                    companyId: $assetPurchaseItem->company_id,
                    branchId: $assetPurchaseItem->branch_id,
                    assetPurchaseId: $assetPurchaseItem->asset_purchase_id,
                    assetPurchaseItemId: $assetPurchaseItem->id,
                    serial: $serial['serial'],
                );
                $this->assetPurchaseItemSerialActions->create($dto);
            }
        }

        $this->flushCache();

        return $assetPurchaseItem->refresh();
    }

    public function delete(AssetPurchaseItem $assetPurchaseItem): bool
    {
        foreach ($assetPurchaseItem->serials as $serial) {
            $this->assetPurchaseItemSerialActions->delete($serial);
        }

        $result = $assetPurchaseItem->delete();

        $this->flushCache();

        return $result;
    }
}
