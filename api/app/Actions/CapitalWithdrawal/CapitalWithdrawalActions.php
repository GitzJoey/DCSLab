<?php

namespace App\Actions\CapitalWithdrawal;

use App\DTOs\ExecuteDTO;
use App\Models\CapitalWithdrawal;
use App\Models\Company;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CapitalWithdrawalActions
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
        ?int $investorId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = CapitalWithdrawal::select('capital_withdrawals.*')
            ->with([
                'company',
                'branch',
                'investor',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'capital_withdrawals.company_id')
            ->whereCompanyId('capital_withdrawals', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $investorId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('capital_withdrawals.branch_id', $branchId);
            }

            if ($investorId) {
                $query->where('capital_withdrawals.investor_id', $investorId);
            }

            if ($cashAccountId) {
                $query->where('capital_withdrawals.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('capital_withdrawals.date', 'desc')
            ->orderBy('capital_withdrawals.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $investorId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_capital_withdrawal_'.implode('_', $cacheParams);

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

    public function read(CapitalWithdrawal $capitalWithdrawal): CapitalWithdrawal
    {
        return $capitalWithdrawal->load([
            'company',
            'branch',
            'investor',
            'cashAccount',
        ]);
    }

    public function create(array $data): CapitalWithdrawal
    {
        $timer_start = microtime(true);

        try {
            $capitalWithdrawal = new CapitalWithdrawal();
            $capitalWithdrawal->company_id = $data['company_id'];
            $capitalWithdrawal->branch_id = $data['branch_id'];
            $capitalWithdrawal->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $capitalWithdrawal->date = $data['date'];
            $capitalWithdrawal->investor_id = $data['investor_id'];
            $capitalWithdrawal->cash_account_id = $data['cash_account_id'];
            $capitalWithdrawal->amount = $data['amount'];
            $capitalWithdrawal->remarks = $data['remarks'];
            $capitalWithdrawal->save();

            $this->flushCache();

            return $capitalWithdrawal;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CapitalWithdrawal $capitalWithdrawal, array $data): CapitalWithdrawal
    {
        $timer_start = microtime(true);

        try {
            $capitalWithdrawal->company_id = $data['company_id'];
            $capitalWithdrawal->branch_id = $data['branch_id'];
            $capitalWithdrawal->code = $this->generateUniqueCode($capitalWithdrawal->company_id, $data['code'], $capitalWithdrawal->id);
            $capitalWithdrawal->date = $data['date'];
            $capitalWithdrawal->investor_id = $data['investor_id'];
            $capitalWithdrawal->cash_account_id = $data['cash_account_id'];
            $capitalWithdrawal->amount = $data['amount'];
            $capitalWithdrawal->remarks = $data['remarks'];
            $capitalWithdrawal->save();

            $this->flushCache();

            return $capitalWithdrawal->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CapitalWithdrawal $capitalWithdrawal): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $capitalWithdrawal->delete();

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
                $count = $company->capitalWithdrawals()->withTrashed()->count() + 1 + $tryCount;
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
        $result = CapitalWithdrawal::whereCompanyId('capital_withdrawals', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
