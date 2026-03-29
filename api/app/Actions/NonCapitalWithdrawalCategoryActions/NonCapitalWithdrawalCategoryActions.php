<?php

namespace App\Actions\NonCapitalWithdrawalCategory;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\NonCapitalWithdrawalCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class NonCapitalWithdrawalCategoryActions
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
        $query = NonCapitalWithdrawalCategory::select('non_capital_withdrawal_categories.*')
            ->with('company')
            ->join('companies', 'companies.id', '=', 'non_capital_withdrawal_categories.company_id')
            ->whereCompanyId('non_capital_withdrawal_categories', $companyId)
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
                $query->orWhere('non_capital_withdrawal_categories.id', $includeId);
            }
        });

        if ($includeId) $query->orderByRaw('FIELD(non_capital_withdrawal_categories.id, '.$includeId.') desc');
        $query->orderBy('companies.name', 'asc')
            ->orderBy('non_capital_withdrawal_categories.name', 'asc')
            ->orderBy('non_capital_withdrawal_categories.id', 'asc');

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

                $cacheKey = 'read_any_non_capital_withdrawal_category_'.implode('_', $cacheParams);

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

    public function read(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory): NonCapitalWithdrawalCategory
    {
        return $nonCapitalWithdrawalCategory->load('company');
    }

    public function create(array $data): NonCapitalWithdrawalCategory
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalWithdrawalCategory = new NonCapitalWithdrawalCategory();
            $nonCapitalWithdrawalCategory->company_id = $data['company_id'];
            $nonCapitalWithdrawalCategory->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $nonCapitalWithdrawalCategory->name = $data['name'];
            $nonCapitalWithdrawalCategory->save();

            $this->flushCache();

            return $nonCapitalWithdrawalCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory, array $data): NonCapitalWithdrawalCategory
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalWithdrawalCategory->company_id = $data['company_id'];
            $nonCapitalWithdrawalCategory->code = $this->generateUniqueCode($nonCapitalWithdrawalCategory->company_id, $data['code'], $nonCapitalWithdrawalCategory->id);
            $nonCapitalWithdrawalCategory->name = $data['name'];
            $nonCapitalWithdrawalCategory->save();

            $this->flushCache();

            return $nonCapitalWithdrawalCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $nonCapitalWithdrawalCategory->delete();

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
                $count = $company->nonCapitalWithdrawalCategories()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = NonCapitalWithdrawalCategory::whereCompanyId('non_capital_withdrawal_categories', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId = null): bool
    {
        $query = NonCapitalWithdrawalCategory::whereCompanyId('non_capital_withdrawal_categories', $companyId)->whereName($name);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
