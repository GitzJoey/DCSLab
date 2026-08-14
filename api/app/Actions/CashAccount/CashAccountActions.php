<?php

namespace App\Actions\CashAccount;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\CashAccountCreateDTO;
use App\DTOs\CashAccountUpdateDTO;
use App\DTOs\CashAccountWithRemainingBalanceDTO;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ChartOfAccountUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\CashAccount;
use App\Models\Company;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class CashAccountActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
    ];

    public function __construct(
        private readonly ChartOfAccountActions $chartOfAccountActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?bool $isBank,
        ?CashAccountWithRemainingBalanceDTO $withRemainingBalance,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = CashAccount::with(self::LIST_EAGER_LOADS)->select('cash_accounts.*')
            ->whereCompanyId('cash_accounts', $companyId)
            ->withTrashed();

        if ($branchId) {
            $query->where('cash_accounts.branch_id', $branchId);
        }

        if ($withRemainingBalance) {
            $endDate = $withRemainingBalance->endDate ? TimezoneHelper::convertToUTC($withRemainingBalance->endDate) : null;

            $query->withRemainingBalance(
                endDate: $endDate
            );
        }

        $query->where(function ($query) use ($withTrashed, $search, $includeId, $isBank) {
            $query->where(function ($query) use ($withTrashed, $search, $isBank) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('cash_accounts.code', 'like', '%'.$search.'%')
                            ->orWhere('cash_accounts.name', 'like', '%'.$search.'%')
                            ->orWhere('cash_accounts.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($isBank)) {
                    $query->where('cash_accounts.is_bank', $isBank);
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
                    is_null($isBank) ? '[null]' : ($isBank ? 'true' : 'false'),
                    $withRemainingBalance?->endDate ?? '[null]',
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
        return $cashAccount->load(self::LIST_EAGER_LOADS);
    }

    public function create(CashAccountCreateDTO $data): CashAccount
    {
        $timer_start = microtime(true);

        try {
            $cashAccount = new CashAccount();
            $cashAccount->company_id = $data->companyId;
            $cashAccount->branch_id = $data->branchId;
            $cashAccount->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $cashAccount->name = $data->name;
            $cashAccount->is_bank = $data->isBank;
            $cashAccount->is_active = $data->isActive;
            $cashAccount->remarks = $data->remarks;
            $cashAccount->save();

            $parentChartOfAccount = (function () use ($cashAccount) {
                if ($cashAccount->is_bank) {
                    return $cashAccount->company->assetCurrentBankChartOfAccount;
                }

                return $cashAccount->company->assetCurrentCashChartOfAccount;
            })();
            if (! $parentChartOfAccount) {
                throw new InvalidArgumentException('Cash account chart of account parent must exist in company.');
            }

            $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $cashAccount->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $parentChartOfAccount->id,
                sourceType: CashAccount::class,
                sourceId: $cashAccount->id,
                code: $parentChartOfAccount->code.'.'.$cashAccount->code,
                name: $cashAccount->name,
                normalBalance: 'debit',
                isGroup: false,
                isActive: $cashAccount->is_active,
                remarks: $cashAccount->remarks,
            );
            $this->chartOfAccountActions->create($chartOfAccountDTO);

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

    public function update(CashAccount $cashAccount, CashAccountUpdateDTO $data): CashAccount
    {
        $timer_start = microtime(true);

        try {
            $cashAccount->code = $this->generateUniqueCode($cashAccount->company_id, $data->code, $cashAccount->id);
            $cashAccount->name = $data->name;
            $cashAccount->is_bank = $data->isBank;
            $cashAccount->is_active = $data->isActive;
            $cashAccount->remarks = $data->remarks;
            $cashAccount->save();

            $parentChartOfAccount = (function () use ($cashAccount) {
                if ($cashAccount->is_bank) {
                    return $cashAccount->company->assetCurrentBankChartOfAccount;
                }

                return $cashAccount->company->assetCurrentCashChartOfAccount;
            })();
            if (! $parentChartOfAccount) {
                throw new InvalidArgumentException('Cash account chart of account parent must exist in company.');
            }

            $chartOfAccount = $cashAccount->chartOfAccount;
            if ($chartOfAccount) {
                $chartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $parentChartOfAccount->id,
                    code: $parentChartOfAccount->code.'.'.$cashAccount->code,
                    name: $cashAccount->name,
                    normalBalance: 'debit',
                    isGroup: false,
                    isActive: $cashAccount->is_active,
                    remarks: $cashAccount->remarks,
                );
                $this->chartOfAccountActions->update($chartOfAccount, $chartOfAccountDTO);
            } else {
                $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $cashAccount->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $parentChartOfAccount->id,
                    sourceType: CashAccount::class,
                    sourceId: $cashAccount->id,
                    code: $parentChartOfAccount->code.'.'.$cashAccount->code,
                    name: $cashAccount->name,
                    normalBalance: 'debit',
                    isGroup: false,
                    isActive: $cashAccount->is_active,
                    remarks: $cashAccount->remarks,
                );
                $this->chartOfAccountActions->create($chartOfAccountDTO);
            }

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
            $chartOfAccount = $cashAccount->chartOfAccount;
            if ($chartOfAccount) $this->chartOfAccountActions->delete($chartOfAccount);

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
