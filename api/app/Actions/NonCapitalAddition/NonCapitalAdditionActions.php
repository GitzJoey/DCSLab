<?php

namespace App\Actions\NonCapitalAddition;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\NonCapitalAddition;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class NonCapitalAdditionActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?int $categoryId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = NonCapitalAddition::select('non_capital_additions.*')
            ->with([
                'company',
                'branch',
                'category',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'non_capital_additions.company_id')
            ->whereCompanyId('non_capital_additions', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $categoryId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('non_capital_additions.branch_id', $branchId);
            }

            if ($categoryId) {
                $query->where('non_capital_additions.category_id', $categoryId);
            }

            if ($cashAccountId) {
                $query->where('non_capital_additions.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('non_capital_additions.date', 'desc')
            ->orderBy('non_capital_additions.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $categoryId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_non_capital_addition_'.implode('_', $cacheParams);

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

    public function read(NonCapitalAddition $nonCapitalAddition): NonCapitalAddition
    {
        return $nonCapitalAddition->load([
            'company',
            'branch',
            'category',
            'cashAccount',
        ]);
    }

    public function create(array $data): NonCapitalAddition
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalAddition = new NonCapitalAddition();
            $nonCapitalAddition->company_id = $data['company_id'];
            $nonCapitalAddition->branch_id = $data['branch_id'];
            $nonCapitalAddition->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $nonCapitalAddition->date = $data['date'];
            $nonCapitalAddition->category_id = $data['category_id'];
            $nonCapitalAddition->cash_account_id = $data['cash_account_id'];
            $nonCapitalAddition->amount = $data['amount'];
            $nonCapitalAddition->remarks = $data['remarks'];
            $nonCapitalAddition->save();

            $this->flushCache();

            return $nonCapitalAddition;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(NonCapitalAddition $nonCapitalAddition, array $data): NonCapitalAddition
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalAddition->company_id = $data['company_id'];
            $nonCapitalAddition->branch_id = $data['branch_id'];
            $nonCapitalAddition->code = $this->generateUniqueCode($nonCapitalAddition->company_id, $data['code'], $nonCapitalAddition->id);
            $nonCapitalAddition->date = $data['date'];
            $nonCapitalAddition->category_id = $data['category_id'];
            $nonCapitalAddition->cash_account_id = $data['cash_account_id'];
            $nonCapitalAddition->amount = $data['amount'];
            $nonCapitalAddition->remarks = $data['remarks'];
            $nonCapitalAddition->save();

            $this->flushCache();

            return $nonCapitalAddition->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(NonCapitalAddition $nonCapitalAddition): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $retval = $nonCapitalAddition->delete();

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
                $count = $company->nonCapitalAdditions()->withTrashed()->count() + 1 + $tryCount;
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
        $result = NonCapitalAddition::whereCompanyId('non_capital_additions', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
