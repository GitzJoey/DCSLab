<?php

namespace App\Actions\AssetUnit;

use App\DTOs\AssetUnitCreateDTO;
use App\DTOs\AssetUnitUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\AssetUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class AssetUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,
        ?int $includeId,
        ?ExecuteDTO $execute
    ) {
        $query = AssetUnit::query()
            ->select('asset_units.*')
            ->with(self::LIST_EAGER_LOADS)
            ->withTrashed();

        $query->whereCompanyId('asset_units', $companyId);

        $query->where(function ($query) use ($withTrashed, $search, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search) {
                $query->withoutTrashed();

                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('asset_units.code', 'like', '%'.$search.'%')
                            ->orWhere('asset_units.name', 'like', '%'.$search.'%')
                            ->orWhere('asset_units.description', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('asset_units.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(asset_units.id, '.$includeId.') desc');
        }

        $query->orderBy('asset_units.name', 'asc');
        $query->orderBy('asset_units.code', 'asc');

        if ($execute) {
            $timerStart = microtime(true);
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

    public function read(AssetUnit $assetUnit): AssetUnit
    {
        return $assetUnit->load(self::LIST_EAGER_LOADS);
    }

    public function create(AssetUnitCreateDTO $data): AssetUnit
    {
        $timerStart = microtime(true);

        try {
            $assetUnit = new AssetUnit();
            $assetUnit->company_id = $data->companyId;
            $assetUnit->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $assetUnit->name = $data->name;
            $assetUnit->description = $data->description;
            $assetUnit->save();

            $this->flushCache();

            return $assetUnit;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function update(AssetUnit $assetUnit, AssetUnitUpdateDTO $data): bool
    {
        $timerStart = microtime(true);

        try {
            $assetUnit->code = $this->generateUniqueCode($assetUnit->company_id, $data->code, $assetUnit->id);
            $assetUnit->name = $data->name;
            $assetUnit->description = $data->description;
            $result = $assetUnit->save();

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

    public function delete(AssetUnit $assetUnit): bool
    {
        $timerStart = microtime(true);

        try {
            $result = $assetUnit->delete();

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
        return ! AssetUnit::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }

    public function isUniqueName(int $companyId, string $name, ?int $excludeId): bool
    {
        return ! AssetUnit::query()
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
            $count = AssetUnit::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'AUN'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }
}
