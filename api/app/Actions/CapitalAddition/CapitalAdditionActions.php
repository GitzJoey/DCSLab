<?php

namespace App\Actions\CapitalAddition;

use App\DTOs\ExecuteDTO;
use App\Models\CapitalAddition;
use App\Models\Company;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CapitalAdditionActions
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
        $query = CapitalAddition::select('capital_additions.*')
            ->with([
                'company',
                'branch',
                'investor',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'capital_additions.company_id')
            ->whereCompanyId('capital_additions', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $investorId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('capital_additions.branch_id', $branchId);
            }

            if ($investorId) {
                $query->where('capital_additions.investor_id', $investorId);
            }

            if ($cashAccountId) {
                $query->where('capital_additions.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('capital_additions.date', 'desc')
            ->orderBy('capital_additions.id', 'asc');

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

                $cacheKey = 'read_any_capital_addition_'.implode('_', $cacheParams);

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

    public function read(CapitalAddition $capitalAddition): CapitalAddition
    {
        return $capitalAddition->load([
            'company',
            'branch',
            'investor',
            'cashAccount',
        ]);
    }

    public function create(array $data): CapitalAddition
    {
        $timer_start = microtime(true);

        try {
            $capitalAddition = new CapitalAddition();
            $capitalAddition->company_id = $data['company_id'];
            $capitalAddition->branch_id = $data['branch_id'];
            $capitalAddition->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $capitalAddition->date = $data['date'];
            $capitalAddition->investor_id = $data['investor_id'];
            $capitalAddition->cash_account_id = $data['cash_account_id'];
            $capitalAddition->amount = $data['amount'];
            $capitalAddition->remarks = $data['remarks'];
            $capitalAddition->save();

            $this->flushCache();

            return $capitalAddition;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CapitalAddition $capitalAddition, array $data): CapitalAddition
    {
        $timer_start = microtime(true);

        try {
            $capitalAddition->company_id = $data['company_id'];
            $capitalAddition->branch_id = $data['branch_id'];
            $capitalAddition->code = $this->generateUniqueCode($capitalAddition->company_id, $data['code'], $capitalAddition->id);
            $capitalAddition->date = $data['date'];
            $capitalAddition->investor_id = $data['investor_id'];
            $capitalAddition->cash_account_id = $data['cash_account_id'];
            $capitalAddition->amount = $data['amount'];
            $capitalAddition->remarks = $data['remarks'];
            $capitalAddition->save();

            $this->flushCache();

            return $capitalAddition->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CapitalAddition $capitalAddition): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $capitalAddition->delete();

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
                $count = $company->capitalAdditions()->withTrashed()->count() + 1 + $tryCount;
                $code = 'CA'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = CapitalAddition::whereCompanyId('capital_additions', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
