<?php

namespace App\Actions\AssetPurchase;

use App\Actions\AssetPurchaseItem\AssetPurchaseItemActions;
use App\DTOs\AssetPurchaseCreateDTO;
use App\DTOs\AssetPurchaseItemCreateDTO;
use App\DTOs\AssetPurchaseItemUpdateDTO;
use App\DTOs\AssetPurchaseUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\AssetPurchase;
use App\Models\AssetPurchaseItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Illuminate\Support\Facades\Config;

class AssetPurchaseActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'items.asset.assetCategory',
        'items.asset.assetUnit',
        'items.serials',
    ];

    public function __construct(
        private readonly AssetPurchaseItemActions $assetPurchaseItemActions,
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
        $query = AssetPurchase::select('asset_purchases.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'asset_purchases.company_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'asset_purchases.supplier_id')
            ->whereCompanyId('asset_purchases', $companyId)
            ->whereBranchId('asset_purchases', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_purchases.code', 'like', '%'.$search.'%')
                        ->orWhere('asset_purchases.remarks', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.code', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.name', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('asset_purchases.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('asset_purchases.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }
        });

        $query->orderBy('asset_purchases.date', 'desc');

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

        $cacheKey = 'read_any_asset_purchase_'.implode('_', $cacheParams);

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

    public function read(AssetPurchase $assetPurchase): AssetPurchase
    {
        return $assetPurchase->load(self::DETAIL_EAGER_LOADS);
    }

    public function create(AssetPurchaseCreateDTO $data): AssetPurchase
    {
        $assetPurchase = new AssetPurchase();
        $assetPurchase->company_id = $data->companyId;
        $assetPurchase->branch_id = $data->branchId;
        $assetPurchase->code = $this->generateUniqueCode($data->companyId, $data->code, null);
        $assetPurchase->date = $this->generateDate($data->date);
        $assetPurchase->due_days = $data->dueDays;
        $assetPurchase->supplier_id = $data->supplierId;
        $assetPurchase->remarks = $data->remarks;
        $assetPurchase->is_posted = $data->isPosted;
        $assetPurchase->item_total = 0;
        $assetPurchase->additional_cost = $data->additionalCost;
        $assetPurchase->rounding = $data->rounding;
        $assetPurchase->amount_payable = 0;
        $assetPurchase->save();

        foreach ($data->items as $item) {
            $dto = new AssetPurchaseItemCreateDTO(
                companyId: $assetPurchase->company_id,
                branchId: $assetPurchase->branch_id,
                assetPurchaseId: $assetPurchase->id,
                assetId: $item['asset_id'],
                qty: (string) $item['qty'],
                unitPrice: (string) $item['unit_price'],
                remarks: $item['remarks'],
                serials: $item['serials'],
            );
            $this->assetPurchaseItemActions->create($dto);
        }

        $this->updateSummary($assetPurchase);
        $this->flushCache();

        return $assetPurchase;
    }

    public function update(AssetPurchase $assetPurchase, AssetPurchaseUpdateDTO $data): AssetPurchase
    {
        $assetPurchase->company_id = $data->companyId;
        $assetPurchase->branch_id = $data->branchId;
        $assetPurchase->code = $this->generateUniqueCode($data->companyId, $data->code, $assetPurchase->id);
        $assetPurchase->date = $this->generateDate($data->date);
        $assetPurchase->due_days = $data->dueDays;
        $assetPurchase->supplier_id = $data->supplierId;
        $assetPurchase->remarks = $data->remarks;
        $assetPurchase->is_posted = $data->isPosted;
        $assetPurchase->additional_cost = $data->additionalCost;
        $assetPurchase->rounding = $data->rounding;
        $assetPurchase->save();

        foreach ($data->deleteItemIds as $deleteId) {
            $item = $assetPurchase->items()->findOrFail($deleteId);
            $this->assetPurchaseItemActions->delete($item);
        }

        foreach ($data->items as $item) {
            if (! empty($item['id'])) {
                $existingItem = $assetPurchase->items()->findOrFail($item['id']);
                $dto = new AssetPurchaseItemUpdateDTO(
                    assetId: $item['asset_id'],
                    qty: (string) $item['qty'],
                    unitPrice: (string) $item['unit_price'],
                    remarks: $item['remarks'],
                    deleteSerialIds: $item['delete_serial_ids'],
                    serials: $item['serials'],
                );
                $this->assetPurchaseItemActions->update($existingItem, $dto);
            } else {
                $dto = new AssetPurchaseItemCreateDTO(
                    companyId: $assetPurchase->company_id,
                    branchId: $assetPurchase->branch_id,
                    assetPurchaseId: $assetPurchase->id,
                    assetId: $item['asset_id'],
                    qty: (string) $item['qty'],
                    unitPrice: (string) $item['unit_price'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );
                $this->assetPurchaseItemActions->create($dto);
            }
        }

        $this->updateSummary($assetPurchase);
        $this->flushCache();

        return $assetPurchase;
    }

    private function updateSummary(AssetPurchase $assetPurchase): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, AssetPurchaseItem> $items */
        $items = $assetPurchase->items()->get();

        $itemTotal = (float) $items->sum('subtotal');
        $additionalCost = (float) $assetPurchase->additional_cost;
        $remainingAdditionalCost = $additionalCost;
        $itemsCount = $items->count();

        foreach ($items->values() as $index => $item) {
            $allocatedAdditionalCost = 0.0;

            if ($itemTotal > 0) {
                if ($index === $itemsCount - 1) {
                    $allocatedAdditionalCost = $remainingAdditionalCost;
                } else {
                    $allocatedAdditionalCost = round(($additionalCost * (float) $item->subtotal) / $itemTotal, 8);
                    $remainingAdditionalCost = round($remainingAdditionalCost - $allocatedAdditionalCost, 8);
                }
            }

            $subtotalAfterAdditionalCost = round((float) $item->subtotal + $allocatedAdditionalCost, 8);
            $unitAcquisitionCost = (float) $item->qty > 0
                ? round($subtotalAfterAdditionalCost / (float) $item->qty, 8)
                : 0.0;

            $item->allocated_additional_cost = number_format(round($allocatedAdditionalCost, 8), 8, '.', '');
            $item->subtotal_after_additional_cost = number_format(round($subtotalAfterAdditionalCost, 8), 8, '.', '');
            $item->unit_acquisition_cost = number_format(round($unitAcquisitionCost, 8), 8, '.', '');
            $item->save();
        }

        $assetPurchase->item_total = number_format(round($itemTotal, 8), 8, '.', '');
        $assetPurchase->amount_payable = number_format(
            round($itemTotal + (float) $assetPurchase->additional_cost + (float) $assetPurchase->rounding, 8),
            8,
            '.',
            ''
        );
        $assetPurchase->save();

        $assetPurchase->refresh();
    }

    public function delete(AssetPurchase $assetPurchase): bool
    {
        foreach ($assetPurchase->items as $item) {
            $this->assetPurchaseItemActions->delete($item);
        }

        $result = $assetPurchase->delete();

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
            $count = AssetPurchase::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'AP'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = AssetPurchase::where('company_id', $companyId)
            ->where('code', $code);

        if ($exceptId) {
            $result->where('id', '<>', $exceptId);
        }

        return $result->count() === 0;
    }
}
