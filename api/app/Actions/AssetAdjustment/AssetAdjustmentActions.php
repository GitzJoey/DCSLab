<?php

namespace App\Actions\AssetAdjustment;

use App\Actions\AssetAdjustmentInItem\AssetAdjustmentInItemActions;
use App\Actions\AssetAdjustmentOutItem\AssetAdjustmentOutItemActions;
use App\DTOs\AssetAdjustmentCreateDTO;
use App\DTOs\AssetAdjustmentInItemCreateDTO;
use App\DTOs\AssetAdjustmentInItemUpdateDTO;
use App\DTOs\AssetAdjustmentOutItemCreateDTO;
use App\DTOs\AssetAdjustmentOutItemUpdateDTO;
use App\DTOs\AssetAdjustmentUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\AssetAdjustment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Illuminate\Support\Facades\Config;

class AssetAdjustmentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'inItems.asset.assetCategory',
        'inItems.asset.assetUnit',
        'inItems.serials',
        'outItems.asset.assetCategory',
        'outItems.asset.assetUnit',
        'outItems.serials',
    ];

    public function __construct(
        private readonly AssetAdjustmentInItemActions $assetAdjustmentInItemActions,
        private readonly AssetAdjustmentOutItemActions $assetAdjustmentOutItemActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?ExecuteDTO $execute
    ) {
        $query = AssetAdjustment::select('asset_adjustments.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'asset_adjustments.company_id')
            ->whereCompanyId('asset_adjustments', $companyId)
            ->whereBranchId('asset_adjustments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%'.$search.'%')
                        ->orWhere('remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('asset_adjustments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('asset_adjustments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }
        });

        $query->orderBy('asset_adjustments.date', 'desc');

        if (! $execute) {
            return $query;
        }

        $cacheParams = [
            $withTrashed ? 'true' : 'false',
            $companyId,
            $branchId ?? '[null]',
            empty($search) ? '[empty]' : $search,
            $startDate ?? '[null]',
            $endDate ?? '[null]',
            $execute->pagination ? 'true' : 'false',
            $execute->pagination?->page ?? '[null]',
            $execute->pagination?->perPage ?? '[null]',
            $execute->get?->limit ?? '[null]',
        ];

        $cacheKey = 'read_any_asset_adjustment_'.implode('_', $cacheParams);

        if ($execute->useCache) {
            $cacheResult = $this->readFromCache($cacheKey);
            if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                return $cacheResult;
            }
        }

        $result = $execute->pagination
            ? $query->paginate(
                perPage: $execute->pagination->perPage,
                columns: ['*'],
                pageName: 'page',
                page: $execute->pagination->page,
            )
            : ($execute->get?->limit ? $query->limit($execute->get->limit)->get() : $query->get());

        if ($execute->useCache) {
            $this->saveToCache($cacheKey, $result);
        }

        return $result;
    }

    public function read(AssetAdjustment $assetAdjustment): AssetAdjustment
    {
        return $assetAdjustment->load(self::DETAIL_EAGER_LOADS);
    }

    public function create(AssetAdjustmentCreateDTO $data): AssetAdjustment
    {
        $assetAdjustment = new AssetAdjustment();
        $assetAdjustment->company_id = $data->companyId;
        $assetAdjustment->branch_id = $data->branchId;
        $assetAdjustment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
        $assetAdjustment->date = $this->generateDate($data->date);
        $assetAdjustment->remarks = $data->remarks;
        $assetAdjustment->is_posted = $data->isPosted;
        $assetAdjustment->save();

        $this->saveInItems($assetAdjustment, $data->inItems);
        $this->saveOutItems($assetAdjustment, $data->outItems);

        $this->refreshTotals($assetAdjustment);
        $this->flushCache();

        return $assetAdjustment;
    }

    public function update(AssetAdjustment $assetAdjustment, AssetAdjustmentUpdateDTO $data): AssetAdjustment
    {
        $assetAdjustment->company_id = $data->companyId;
        $assetAdjustment->branch_id = $data->branchId;
        $assetAdjustment->code = $this->generateUniqueCode($data->companyId, $data->code, $assetAdjustment->id);
        $assetAdjustment->date = $this->generateDate($data->date);
        $assetAdjustment->remarks = $data->remarks;
        $assetAdjustment->is_posted = $data->isPosted;
        $assetAdjustment->save();

        $this->updateInItems($assetAdjustment, $data->deleteInItemIds, $data->inItems);
        $this->updateOutItems($assetAdjustment, $data->deleteOutItemIds, $data->outItems);

        $this->refreshTotals($assetAdjustment);
        $this->flushCache();

        return $assetAdjustment->refresh();
    }

    public function delete(AssetAdjustment $assetAdjustment): bool
    {
        foreach ($assetAdjustment->inItems as $inItem) {
            $this->assetAdjustmentInItemActions->delete($inItem);
        }

        foreach ($assetAdjustment->outItems as $outItem) {
            $this->assetAdjustmentOutItemActions->delete($outItem);
        }

        $result = $assetAdjustment->delete();

        $this->flushCache();

        return $result;
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
        if ($code != config('dcslab.KEYWORDS.AUTO')) {
            return $code;
        }

        $tryCount = 0;
        do {
            $count = AssetAdjustment::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'AA'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = AssetAdjustment::where('company_id', $companyId)
            ->where('code', $code);

        if ($exceptId) {
            $result->where('id', '<>', $exceptId);
        }

        return $result->count() === 0;
    }

    private function saveInItems(AssetAdjustment $assetAdjustment, array $inItems): void
    {
        foreach ($inItems as $inItem) {
            $dto = new AssetAdjustmentInItemCreateDTO(
                companyId: $assetAdjustment->company_id,
                branchId: $assetAdjustment->branch_id,
                assetAdjustmentId: $assetAdjustment->id,
                assetId: $inItem['asset_id'],
                qty: $inItem['qty'],
                remarks: $inItem['remarks'],
                serials: $inItem['serials'],
            );
            $this->assetAdjustmentInItemActions->create($dto);
        }
    }

    private function saveOutItems(AssetAdjustment $assetAdjustment, array $outItems): void
    {
        foreach ($outItems as $outItem) {
            $dto = new AssetAdjustmentOutItemCreateDTO(
                companyId: $assetAdjustment->company_id,
                branchId: $assetAdjustment->branch_id,
                assetAdjustmentId: $assetAdjustment->id,
                assetId: $outItem['asset_id'],
                qty: $outItem['qty'],
                remarks: $outItem['remarks'],
                serials: $outItem['serials'],
            );
            $this->assetAdjustmentOutItemActions->create($dto);
        }
    }

    private function updateInItems(AssetAdjustment $assetAdjustment, array $deleteIds, array $inItems): void
    {
        foreach ($deleteIds as $deleteId) {
            $assetAdjustmentInItem = $assetAdjustment->inItems()->findOrFail($deleteId);
            $this->assetAdjustmentInItemActions->delete($assetAdjustmentInItem);
        }

        foreach ($inItems as $inItem) {
            if ($inItem['id']) {
                $assetAdjustmentInItem = $assetAdjustment->inItems()->findOrFail($inItem['id']);
                $dto = new AssetAdjustmentInItemUpdateDTO(
                    assetId: $inItem['asset_id'],
                    qty: $inItem['qty'],
                    remarks: $inItem['remarks'],
                    deleteSerialIds: $inItem['delete_serial_ids'],
                    serials: $inItem['serials'],
                );
                $this->assetAdjustmentInItemActions->update($assetAdjustmentInItem, $dto);
            } else {
                $dto = new AssetAdjustmentInItemCreateDTO(
                    companyId: $assetAdjustment->company_id,
                    branchId: $assetAdjustment->branch_id,
                    assetAdjustmentId: $assetAdjustment->id,
                    assetId: $inItem['asset_id'],
                    qty: $inItem['qty'],
                    remarks: $inItem['remarks'],
                    serials: $inItem['serials'],
                );
                $this->assetAdjustmentInItemActions->create($dto);
            }
        }
    }

    private function updateOutItems(AssetAdjustment $assetAdjustment, array $deleteIds, array $outItems): void
    {
        foreach ($deleteIds as $deleteId) {
            $assetAdjustmentOutItem = $assetAdjustment->outItems()->findOrFail($deleteId);
            $this->assetAdjustmentOutItemActions->delete($assetAdjustmentOutItem);
        }

        foreach ($outItems as $outItem) {
            if ($outItem['id']) {
                $assetAdjustmentOutItem = $assetAdjustment->outItems()->findOrFail($outItem['id']);
                $dto = new AssetAdjustmentOutItemUpdateDTO(
                    assetId: $outItem['asset_id'],
                    qty: $outItem['qty'],
                    remarks: $outItem['remarks'],
                    deleteSerialIds: $outItem['delete_serial_ids'],
                    serials: $outItem['serials'],
                );
                $this->assetAdjustmentOutItemActions->update($assetAdjustmentOutItem, $dto);
            } else {
                $dto = new AssetAdjustmentOutItemCreateDTO(
                    companyId: $assetAdjustment->company_id,
                    branchId: $assetAdjustment->branch_id,
                    assetAdjustmentId: $assetAdjustment->id,
                    assetId: $outItem['asset_id'],
                    qty: $outItem['qty'],
                    remarks: $outItem['remarks'],
                    serials: $outItem['serials'],
                );
                $this->assetAdjustmentOutItemActions->create($dto);
            }
        }
    }

    private function refreshTotals(AssetAdjustment $assetAdjustment): void
    {
        $assetAdjustment->total_incoming_asset_qty = $assetAdjustment->inItems()->sum('qty');
        $assetAdjustment->total_outgoing_asset_qty = $assetAdjustment->outItems()->sum('qty');
        $assetAdjustment->save();
    }
}
