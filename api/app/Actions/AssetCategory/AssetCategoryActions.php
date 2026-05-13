<?php

namespace App\Actions\AssetCategory;

use App\DTOs\AssetCategoryCreateDTO;
use App\DTOs\AssetCategoryUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,

        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = AssetCategory::with(self::LIST_EAGER_LOADS)
            ->select('asset_categories.*')
            ->whereCompanyId('asset_categories', $companyId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('asset_categories.code', 'like', '%'.$search.'%')
                            ->orWhere('asset_categories.name', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('asset_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(asset_categories.id, '.$includeId.') desc');
        }

        $query->orderBy('asset_categories.name', 'asc')
            ->orderBy('asset_categories.code', 'asc')
            ->orderBy('asset_categories.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_asset_category_'.implode('-', $cacheParams);

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

        return $query;
    }

    public function read(AssetCategory $assetCategory): AssetCategory
    {
        return $assetCategory->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetCategoryCreateDTO $data): AssetCategory
    {
        $timer_start = microtime(true);

        try {
            $assetCategory = new AssetCategory();
            $assetCategory->company_id = $data->companyId;
            $assetCategory->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $assetCategory->name = $data->name;
            $assetCategory->estimated_useful_life_months = $data->estimatedUsefulLifeMonths;
            $assetCategory->remarks = $data->remarks;
            $assetCategory->save();

            $this->flushCache();

            return $assetCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(AssetCategory $assetCategory, AssetCategoryUpdateDTO $data): AssetCategory
    {
        $timer_start = microtime(true);

        try {
            $assetCategory->code = $this->generateUniqueCode(
                $assetCategory->company_id,
                $data->code,
                $assetCategory->id,
            );
            $assetCategory->name = $data->name;
            $assetCategory->estimated_useful_life_months = $data->estimatedUsefulLifeMonths;
            $assetCategory->remarks = $data->remarks;
            $assetCategory->save();

            $this->flushCache();

            return $assetCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(AssetCategory $assetCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $assetCategory->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != config('dcslab.KEYWORDS.AUTO')) {
            return $code;
        }

        $tryCount = 0;
        do {
            $count = AssetCategory::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'ACT'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $query = AssetCategory::where('company_id', $companyId)
            ->where('name', '=', $name);

        if ($exceptId) {
            $query->where('asset_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = AssetCategory::where('company_id', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $query->where('asset_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
