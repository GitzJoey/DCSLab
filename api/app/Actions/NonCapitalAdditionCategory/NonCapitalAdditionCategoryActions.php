<?php

namespace App\Actions\NonCapitalAdditionCategory;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\NonCapitalAdditionCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class NonCapitalAdditionCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

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
        $query = NonCapitalAdditionCategory::select('non_capital_addition_categories.*')
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'non_capital_addition_categories.company_id')
            ->whereCompanyId('non_capital_addition_categories', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->search($search);
                }
            });

            if ($includeId) {
                $query->orWhere('non_capital_addition_categories.id', $includeId);
            }
        });

        if ($includeId) $query->orderByRaw('FIELD(non_capital_addition_categories.id, '.$includeId.') desc');
        $query->orderBy('companies.name', 'asc')
            ->orderBy('non_capital_addition_categories.name', 'asc')
            ->orderBy('non_capital_addition_categories.id', 'asc');

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

                $cacheKey = 'read_any_non_capital_addition_category_'.implode('_', $cacheParams);

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

        return $query;
    }

    public function read(NonCapitalAdditionCategory $nonCapitalAdditionCategory): NonCapitalAdditionCategory
    {
        return $nonCapitalAdditionCategory->load('company');
    }

    public function create(array $data): NonCapitalAdditionCategory
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalAdditionCategory = new NonCapitalAdditionCategory();
            $nonCapitalAdditionCategory->company_id = $data['company_id'];
            $nonCapitalAdditionCategory->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $nonCapitalAdditionCategory->name = $data['name'];
            $nonCapitalAdditionCategory->save();

            $this->flushCache();

            return $nonCapitalAdditionCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(NonCapitalAdditionCategory $nonCapitalAdditionCategory, array $data): NonCapitalAdditionCategory
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalAdditionCategory->company_id = $data['company_id'];
            $nonCapitalAdditionCategory->code = $this->generateUniqueCode($nonCapitalAdditionCategory->company_id, $data['code'], $nonCapitalAdditionCategory->id);
            $nonCapitalAdditionCategory->name = $data['name'];
            $nonCapitalAdditionCategory->save();

            $this->flushCache();

            return $nonCapitalAdditionCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(NonCapitalAdditionCategory $nonCapitalAdditionCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $nonCapitalAdditionCategory->delete();

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
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->nonCapitalAdditionCategories()->withTrashed()->count() + 1 + $tryCount;
                $code = 'NCAC'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = NonCapitalAdditionCategory::whereCompanyId('non_capital_addition_categories', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId = null): bool
    {
        $query = NonCapitalAdditionCategory::whereCompanyId('non_capital_addition_categories', $companyId)->whereName($name);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
