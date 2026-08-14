<?php

namespace App\Actions\Asset;

use App\DTOs\AssetCreateDTO;
use App\DTOs\AssetUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\Asset;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'assetCategory',
        'assetUnit',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,
        ?int $assetCategoryId,
        ?int $assetUnitId,
        ?int $status,
        ?int $includeId,
        ?ExecuteDTO $execute
    ) {
        $query = Asset::query()
            ->select('assets.*')
            ->with(self::LIST_EAGER_LOADS)
            ->withTrashed();

        $query->join('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id');
        $query->join('asset_units', 'assets.asset_unit_id', '=', 'asset_units.id');
        $query->whereCompanyId('assets', $companyId);

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $assetCategoryId,
            $assetUnitId,
            $status,
            $includeId
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $assetCategoryId,
                $assetUnitId,
                $status
            ) {
                $query->withoutTrashed();

                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('assets.code', 'like', '%'.$search.'%')
                            ->orWhere('assets.name', 'like', '%'.$search.'%')
                            ->orWhere('assets.remarks', 'like', '%'.$search.'%')
                            ->orWhere('asset_categories.code', 'like', '%'.$search.'%')
                            ->orWhere('asset_categories.name', 'like', '%'.$search.'%')
                            ->orWhere('asset_units.code', 'like', '%'.$search.'%')
                            ->orWhere('asset_units.name', 'like', '%'.$search.'%');
                    });
                }

                if ($assetCategoryId) {
                    $query->where('assets.asset_category_id', $assetCategoryId);
                }

                if ($assetUnitId) {
                    $query->where('assets.asset_unit_id', $assetUnitId);
                }

                if (! is_null($status)) {
                    $query->where('assets.status', $status);
                }
            });

            if ($includeId) {
                $query->orWhere('assets.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(assets.id, '.$includeId.') desc');
        }

        $query->orderBy('asset_categories.name', 'asc');
        $query->orderBy('asset_units.name', 'asc');
        $query->orderBy('assets.status', 'desc');
        $query->orderBy('assets.name', 'asc');

        if ($execute) {
            $timerStart = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $assetCategoryId ?? '[null]',
                    $assetUnitId ?? '[null]',
                    $status ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_'.implode('-', $cacheParams);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheData;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page,
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
                $executionTime = microtime(true) - $timerStart;
                $this->loggerPerformance(__METHOD__, $executionTime, $recordsCount);
            }
        }

        return $query;
    }

    public function read(Asset $asset): Asset
    {
        return $asset->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetCreateDTO $data): Asset
    {
        $timerStart = microtime(true);

        try {
            $asset = new Asset();
            $asset->company_id = $data->companyId;
            $asset->asset_category_id = $data->assetCategoryId;
            $asset->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $asset->name = $data->name;
            $asset->asset_unit_id = $data->assetUnitId;
            $asset->status = $data->status;
            $asset->remarks = $data->remarks;
            $asset->save();

            $this->flushCache();

            return $asset;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function update(Asset $asset, AssetUpdateDTO $data): bool
    {
        $timerStart = microtime(true);

        try {
            $asset->asset_category_id = $data->assetCategoryId;
            $asset->code = $this->generateUniqueCode($asset->company_id, $data->code, $asset->id);
            $asset->name = $data->name;
            $asset->asset_unit_id = $data->assetUnitId;
            $asset->status = $data->status;
            $asset->remarks = $data->remarks;
            $result = $asset->save();

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function delete(Asset $asset): bool
    {
        $timerStart = microtime(true);

        try {
            $result = $asset->delete();

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $excludeId): bool
    {
        return ! Asset::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }

    public function isUniqueName(int $companyId, string $name, ?int $excludeId): bool
    {
        return ! Asset::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != config('dcslab.KEYWORDS.AUTO')) {
            return $code;
        }

        $tryCount = 0;
        do {
            $count = Asset::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'AST'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }
}
