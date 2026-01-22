<?php

namespace App\Actions\CashAccount;

use App\DTOs\ExecuteDTO;
use App\Models\CashAccount;
use App\Models\Company;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CashAccountActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): CashAccount
    {
        $timer_start = microtime(true);

        try {
            $cashAccount = new CashAccount();
            $cashAccount->company_id = $data['company_id'];
            $cashAccount->branch_id = $data['branch_id'];
            $cashAccount->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $cashAccount->name = $data['name'];
            $cashAccount->is_bank = $data['is_bank'];
            $cashAccount->is_active = $data['is_active'];
            $cashAccount->remarks = $data['remarks'];
            $cashAccount->save();

            $this->flushCache();

            return $cashAccount;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        bool $withTrashed,

        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = CashAccount::with('company', 'branch')->select('cash_accounts.*')
            ->whereCompanyId($companyId)
            ->withTrashed();

        if ($branchId) {
            $query->where('cash_accounts.branch_id', $branchId);
        }

        $query->where(function ($query) use ($withTrashed, $search, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }
            });

            if ($includeId) {
                $query->orWhere('cash_accounts.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(cash_accounts.id, '.$includeId.') desc');
        }
        $query->orderBy('cash_accounts.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    empty($search) ? '[empty]' : $search,
                    $companyId,
                    $branchId ?? '[null]',
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

    public function read(CashAccount $cashAccount): CashAccount
    {
        return $cashAccount->load('company', 'branch');
    }

    public function update(CashAccount $cashAccount, array $data): CashAccount
    {
        $timer_start = microtime(true);

        try {
            $cashAccount->code = $this->generateUniqueCode($cashAccount->company_id, $data['code'], $cashAccount->id);
            $cashAccount->name = $data['name'];
            $cashAccount->is_bank = $data['is_bank'];
            $cashAccount->is_active = $data['is_active'];
            $cashAccount->remarks = $data['remarks'];
            $cashAccount->save();

            $this->flushCache();

            return $cashAccount->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CashAccount $cashAccount): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $cashAccount->delete();

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

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->cashAccounts()->withTrashed()->count() + 1 + $tryCount;
            $code = 'CAC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->cashAccounts()->count() == 0) {
            return true;
        }

        $query = $company->cashAccounts()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('cash_accounts.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->cashAccounts()->count() == 0) {
            return true;
        }

        $query = $company->cashAccounts()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('cash_accounts.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
