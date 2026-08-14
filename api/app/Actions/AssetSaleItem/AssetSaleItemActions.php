<?php

namespace App\Actions\AssetSaleItem;

use App\Actions\AssetSaleItemSerial\AssetSaleItemSerialActions;
use App\DTOs\AssetSaleItemCreateDTO;
use App\DTOs\AssetSaleItemSerialCreateDTO;
use App\DTOs\AssetSaleItemSerialUpdateDTO;
use App\DTOs\AssetSaleItemUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetSaleItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetSaleItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'assetSale.customer',
        'asset.assetCategory',
        'asset.assetUnit',
        'serials',
    ];

    public function __construct(
        private readonly AssetSaleItemSerialActions $assetSaleItemSerialActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $assetSaleId,
        ?int $assetId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetSaleItem::select('asset_sale_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'asset_sale_items.company_id')
            ->join('asset_sales', 'asset_sales.id', '=', 'asset_sale_items.asset_sale_id')
            ->join('assets', 'assets.id', '=', 'asset_sale_items.asset_id')
            ->whereCompanyId('asset_sale_items', $companyId)
            ->whereBranchId('asset_sale_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetSaleId,
            $assetId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_sales.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.code', 'like', '%'.$search.'%')
                        ->orWhere('assets.name', 'like', '%'.$search.'%')
                        ->orWhere('asset_sale_items.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($assetSaleId) {
                $query->where('asset_sale_items.asset_sale_id', $assetSaleId);
            }

            if ($assetId) {
                $query->where('asset_sale_items.asset_id', $assetId);
            }
        });

        $query->orderBy('asset_sales.date', 'desc')
            ->orderBy('asset_sale_items.id', 'asc');

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
                $assetId ?? '[null]',
                $execute->pagination ? 'true' : 'false',
                $execute->pagination?->page ?? '[null]',
                $execute->pagination?->perPage ?? '[null]',
                $execute->get?->limit ?? '[null]',
            ];

            $cacheKey = 'read_any_asset_sale_item_'.implode('_', $cacheParams);

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

    public function read(AssetSaleItem $assetSaleItem): AssetSaleItem
    {
        return $assetSaleItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetSaleItemCreateDTO $data): AssetSaleItem
    {
        $assetSaleItem = new AssetSaleItem();
        $assetSaleItem->company_id = $data->companyId;
        $assetSaleItem->branch_id = $data->branchId;
        $assetSaleItem->asset_sale_id = $data->assetSaleId;
        $assetSaleItem->asset_id = $data->assetId;
        $assetSaleItem->qty = $data->qty;
        $assetSaleItem->unit_price = $data->unitPrice;
        $assetSaleItem->subtotal = number_format(round((float) $data->qty * (float) $data->unitPrice, 8), 8, '.', '');
        $assetSaleItem->remarks = $data->remarks;
        $assetSaleItem->save();

        foreach ($data->serials as $serial) {
            $dto = new AssetSaleItemSerialCreateDTO(
                companyId: $assetSaleItem->company_id,
                branchId: $assetSaleItem->branch_id,
                assetSaleId: $assetSaleItem->asset_sale_id,
                assetSaleItemId: $assetSaleItem->id,
                serial: $serial['serial'],
            );
            $this->assetSaleItemSerialActions->create($dto);
        }

        $this->flushCache();

        return $assetSaleItem;
    }

    public function update(AssetSaleItem $assetSaleItem, AssetSaleItemUpdateDTO $data): AssetSaleItem
    {
        $assetSaleItem->asset_id = $data->assetId;
        $assetSaleItem->qty = $data->qty;
        $assetSaleItem->unit_price = $data->unitPrice;
        $assetSaleItem->subtotal = number_format(round((float) $data->qty * (float) $data->unitPrice, 8), 8, '.', '');
        $assetSaleItem->remarks = $data->remarks;
        $assetSaleItem->save();

        foreach ($data->deleteSerialIds as $deleteId) {
            $serial = $assetSaleItem->serials()->findOrFail($deleteId);
            $this->assetSaleItemSerialActions->delete($serial);
        }

        foreach ($data->serials as $serial) {
            if ($serial['id']) {
                $serialRow = $assetSaleItem->serials()->findOrFail($serial['id']);
                $dto = new AssetSaleItemSerialUpdateDTO(
                    companyId: $assetSaleItem->company_id,
                    serial: $serial['serial'],
                );
                $this->assetSaleItemSerialActions->update($serialRow, $dto);
            } else {
                $dto = new AssetSaleItemSerialCreateDTO(
                    companyId: $assetSaleItem->company_id,
                    branchId: $assetSaleItem->branch_id,
                    assetSaleId: $assetSaleItem->asset_sale_id,
                    assetSaleItemId: $assetSaleItem->id,
                    serial: $serial['serial'],
                );
                $this->assetSaleItemSerialActions->create($dto);
            }
        }

        $this->flushCache();

        return $assetSaleItem->refresh();
    }

    public function delete(AssetSaleItem $assetSaleItem): bool
    {
        foreach ($assetSaleItem->serials as $serial) {
            $this->assetSaleItemSerialActions->delete($serial);
        }

        $result = $assetSaleItem->delete();
        $this->flushCache();

        return $result;
    }
}
