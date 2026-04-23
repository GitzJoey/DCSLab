<?php

namespace App\Actions\ReceivableCategory;

use App\DTOs\ExecuteDTO;
use App\Models\ReceivableCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ReceivableCategoryActions
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
        $query = ReceivableCategory::with(self::LIST_EAGER_LOADS)
            ->select('receivable_categories.*')
            ->whereCompanyId('receivable_categories', $companyId)
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
                        $query->where('receivable_categories.code', 'like', '%'.$search.'%')
                            ->orWhere('receivable_categories.name', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('receivable_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(receivable_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('receivable_categories.sequence', 'asc')
            ->orderBy('receivable_categories.id', 'asc');

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

                $cacheKey = 'readAny_receivable_category_'.implode('-', $cacheParams);

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

    public function read(ReceivableCategory $receivableCategory): ReceivableCategory
    {
        return $receivableCategory->load(self::LIST_EAGER_LOADS);
    }

    public function create(array $data): ReceivableCategory
    {
        $timer_start = microtime(true);

        try {
            $receivableCategory = new ReceivableCategory();
            $receivableCategory->company_id = $data['company_id'];
            $receivableCategory->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $receivableCategory->name = $data['name'];
            $receivableCategory->sequence = $data['sequence'];
            $receivableCategory->save();

            $this->flushCache();

            return $receivableCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(ReceivableCategory $receivableCategory, array $data): ReceivableCategory
    {
        $timer_start = microtime(true);

        try {
            $receivableCategory->code = $this->generateUniqueCode(
                $receivableCategory->company_id,
                $data['code'],
                $receivableCategory->id,
            );
            $receivableCategory->name = $data['name'];
            $receivableCategory->sequence = $data['sequence'];
            $receivableCategory->save();

            $this->flushCache();

            return $receivableCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(ReceivableCategory $receivableCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $receivableCategory->delete();

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
            $count = ReceivableCategory::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'RCT'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $query = ReceivableCategory::where('company_id', $companyId)
            ->where('name', '=', $name);
        if ($exceptId) {
            $query->where('receivable_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = ReceivableCategory::where('company_id', $companyId)
            ->where('code', '=', $code);
        if ($exceptId) {
            $query->where('receivable_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
