<?php

namespace App\Actions\AssetSale;

use App\Actions\AssetSaleItem\AssetSaleItemActions;
use App\DTOs\AssetSaleCreateDTO;
use App\DTOs\AssetSaleItemCreateDTO;
use App\DTOs\AssetSaleItemUpdateDTO;
use App\DTOs\AssetSaleUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\AssetSale;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Illuminate\Support\Facades\Config;

class AssetSaleActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'items.asset.assetCategory',
        'items.asset.assetUnit',
        'items.serials',
    ];

    public function __construct(
        private readonly AssetSaleItemActions $assetSaleItemActions,
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
        $query = AssetSale::select('asset_sales.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'asset_sales.company_id')
            ->leftJoin('customers', 'customers.id', '=', 'asset_sales.customer_id')
            ->whereCompanyId('asset_sales', $companyId)
            ->whereBranchId('asset_sales', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('asset_sales.code', 'like', '%'.$search.'%')
                        ->orWhere('asset_sales.remarks', 'like', '%'.$search.'%')
                        ->orWhere('customers.code', 'like', '%'.$search.'%')
                        ->orWhere('customers.name', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('asset_sales.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('asset_sales.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }
        });

        $query->orderBy('asset_sales.date', 'desc');

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

        $cacheKey = 'read_any_asset_sale_'.implode('_', $cacheParams);

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

    public function read(AssetSale $assetSale): AssetSale
    {
        return $assetSale->load(self::DETAIL_EAGER_LOADS);
    }

    public function create(AssetSaleCreateDTO $data): AssetSale
    {
        $assetSale = new AssetSale();
        $assetSale->company_id = $data->companyId;
        $assetSale->branch_id = $data->branchId;
        $assetSale->code = $this->generateUniqueCode($data->companyId, $data->code, null);
        $assetSale->date = $this->generateDate($data->date);
        $assetSale->due_days = $data->dueDays;
        $assetSale->customer_id = $data->customerId;
        $assetSale->remarks = $data->remarks;
        $assetSale->is_posted = $data->isPosted;
        $assetSale->item_total = 0;
        $assetSale->rounding = $data->rounding;
        $assetSale->amount_receivable = 0;
        $assetSale->save();

        foreach ($data->items as $item) {
            $dto = new AssetSaleItemCreateDTO(
                companyId: $assetSale->company_id,
                branchId: $assetSale->branch_id,
                assetSaleId: $assetSale->id,
                assetId: $item['asset_id'],
                qty: (string) $item['qty'],
                unitPrice: (string) $item['unit_price'],
                remarks: $item['remarks'],
                serials: $item['serials'],
            );
            $this->assetSaleItemActions->create($dto);
        }

        $this->updateSummary($assetSale);
        $this->flushCache();

        return $assetSale;
    }

    public function update(AssetSale $assetSale, AssetSaleUpdateDTO $data): AssetSale
    {
        $assetSale->company_id = $data->companyId;
        $assetSale->branch_id = $data->branchId;
        $assetSale->code = $this->generateUniqueCode($data->companyId, $data->code, $assetSale->id);
        $assetSale->date = $this->generateDate($data->date);
        $assetSale->due_days = $data->dueDays;
        $assetSale->customer_id = $data->customerId;
        $assetSale->remarks = $data->remarks;
        $assetSale->is_posted = $data->isPosted;
        $assetSale->rounding = $data->rounding;
        $assetSale->save();

        foreach ($data->deleteItemIds as $deleteId) {
            $item = $assetSale->items()->findOrFail($deleteId);
            $this->assetSaleItemActions->delete($item);
        }

        foreach ($data->items as $item) {
            if (! empty($item['id'])) {
                $existingItem = $assetSale->items()->findOrFail($item['id']);
                $dto = new AssetSaleItemUpdateDTO(
                    assetId: $item['asset_id'],
                    qty: (string) $item['qty'],
                    unitPrice: (string) $item['unit_price'],
                    remarks: $item['remarks'],
                    deleteSerialIds: $item['delete_serial_ids'],
                    serials: $item['serials'],
                );
                $this->assetSaleItemActions->update($existingItem, $dto);
            } else {
                $dto = new AssetSaleItemCreateDTO(
                    companyId: $assetSale->company_id,
                    branchId: $assetSale->branch_id,
                    assetSaleId: $assetSale->id,
                    assetId: $item['asset_id'],
                    qty: (string) $item['qty'],
                    unitPrice: (string) $item['unit_price'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );
                $this->assetSaleItemActions->create($dto);
            }
        }

        $this->updateSummary($assetSale);
        $this->flushCache();

        return $assetSale;
    }

    private function updateSummary(AssetSale $assetSale): void
    {
        $itemTotal = (float) $assetSale->items()->sum('subtotal');

        $assetSale->item_total = number_format(round($itemTotal, 8), 8, '.', '');
        $assetSale->amount_receivable = number_format(round($itemTotal + (float) $assetSale->rounding, 8), 8, '.', '');
        $assetSale->save();

        $assetSale->refresh();
    }

    public function delete(AssetSale $assetSale): bool
    {
        foreach ($assetSale->items as $item) {
            $this->assetSaleItemActions->delete($item);
        }

        $result = $assetSale->delete();
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
            $count = AssetSale::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'AS'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = AssetSale::where('company_id', $companyId)
            ->where('code', $code);

        if ($exceptId) {
            $result->where('id', '<>', $exceptId);
        }

        return $result->count() === 0;
    }
}
