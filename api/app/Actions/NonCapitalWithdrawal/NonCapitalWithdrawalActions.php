<?php

namespace App\Actions\NonCapitalWithdrawal;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\NonCapitalWithdrawal;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class NonCapitalWithdrawalActions
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
        $query = NonCapitalWithdrawal::select('non_capital_withdrawals.*')
            ->with([
                'company',
                'branch',
                'category',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'non_capital_withdrawals.company_id')
            ->whereCompanyId('non_capital_withdrawals', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $categoryId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('non_capital_withdrawals.branch_id', $branchId);
            }

            if ($categoryId) {
                $query->where('non_capital_withdrawals.category_id', $categoryId);
            }

            if ($cashAccountId) {
                $query->where('non_capital_withdrawals.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('non_capital_withdrawals.date', 'desc')
            ->orderBy('non_capital_withdrawals.id', 'asc');

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

                $cacheKey = 'read_any_non_capital_withdrawal_'.implode('_', $cacheParams);

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

    public function read(NonCapitalWithdrawal $nonCapitalWithdrawal): NonCapitalWithdrawal
    {
        return $nonCapitalWithdrawal->load([
            'company',
            'branch',
            'category',
            'cashAccount',
        ]);
    }

    public function create(array $data): NonCapitalWithdrawal
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalWithdrawal = new NonCapitalWithdrawal();
            $nonCapitalWithdrawal->company_id = $data['company_id'];
            $nonCapitalWithdrawal->branch_id = $data['branch_id'];
            $nonCapitalWithdrawal->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $nonCapitalWithdrawal->date = $data['date'];
            $nonCapitalWithdrawal->category_id = $data['category_id'];
            $nonCapitalWithdrawal->cash_account_id = $data['cash_account_id'];
            $nonCapitalWithdrawal->amount = $data['amount'];
            $nonCapitalWithdrawal->remarks = $data['remarks'];
            $nonCapitalWithdrawal->save();

            $this->flushCache();

            return $nonCapitalWithdrawal;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(NonCapitalWithdrawal $nonCapitalWithdrawal, array $data): NonCapitalWithdrawal
    {
        $timer_start = microtime(true);

        try {
            $nonCapitalWithdrawal->company_id = $data['company_id'];
            $nonCapitalWithdrawal->branch_id = $data['branch_id'];
            $nonCapitalWithdrawal->code = $this->generateUniqueCode($nonCapitalWithdrawal->company_id, $data['code'], $nonCapitalWithdrawal->id);
            $nonCapitalWithdrawal->date = $data['date'];
            $nonCapitalWithdrawal->category_id = $data['category_id'];
            $nonCapitalWithdrawal->cash_account_id = $data['cash_account_id'];
            $nonCapitalWithdrawal->amount = $data['amount'];
            $nonCapitalWithdrawal->remarks = $data['remarks'];
            $nonCapitalWithdrawal->save();

            $this->flushCache();

            return $nonCapitalWithdrawal->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(NonCapitalWithdrawal $nonCapitalWithdrawal): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $retval = $nonCapitalWithdrawal->delete();

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
                $count = $company->nonCapitalWithdrawals()->withTrashed()->count() + 1 + $tryCount;
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
        $result = NonCapitalWithdrawal::whereCompanyId('non_capital_withdrawals', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
