<?php

namespace App\Actions\LiabilityCategory;

use App\DTOs\ExecuteDTO;
use App\Models\LiabilityCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class LiabilityCategoryActions
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
        $query = LiabilityCategory::with(self::LIST_EAGER_LOADS)
            ->select('liability_categories.*')
            ->whereCompanyId('liability_categories', $companyId)
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
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('liability_categories.code', 'like', '%'.$search.'%')
                            ->orWhere('liability_categories.name', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('liability_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(liability_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('liability_categories.sequence', 'asc')
            ->orderBy('liability_categories.id', 'asc');

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

                $cacheKey = 'readAny_liability_category_'.implode('-', $cacheParams);

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

    public function read(LiabilityCategory $liabilityCategory): LiabilityCategory
    {
        return $liabilityCategory->load(self::LIST_EAGER_LOADS);
    }

    public function create(array $data): LiabilityCategory
    {
        $timer_start = microtime(true);

        try {
            $liabilityCategory = new LiabilityCategory();
            $liabilityCategory->company_id = $data['company_id'];
            $liabilityCategory->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $liabilityCategory->name = $data['name'];
            $liabilityCategory->sequence = $data['sequence'];
            $liabilityCategory->save();

            $this->flushCache();

            return $liabilityCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(LiabilityCategory $liabilityCategory, array $data): LiabilityCategory
    {
        $timer_start = microtime(true);

        try {
            $liabilityCategory->code = $this->generateUniqueCode(
                $liabilityCategory->company_id,
                $data['code'],
                $liabilityCategory->id,
            );
            $liabilityCategory->name = $data['name'];
            $liabilityCategory->sequence = $data['sequence'];
            $liabilityCategory->save();

            $this->flushCache();

            return $liabilityCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(LiabilityCategory $liabilityCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $liabilityCategory->delete();

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
        if ($code != config('dcslab.KEYWORDS.AUTO')) return $code;

        $tryCount = 0;
        do {
            $count = LiabilityCategory::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'LCT'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $query = LiabilityCategory::where('company_id', $companyId)
            ->where('name', '=', $name);
        if ($exceptId) {
            $query->where('liability_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = LiabilityCategory::where('company_id', $companyId)
            ->where('code', '=', $code);
        if ($exceptId) {
            $query->where('liability_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
