<?php

namespace App\Actions\Investor;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ChartOfAccountUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\InvestorCreateDTO;
use App\DTOs\InvestorUpdateDTO;
use App\Models\Company;
use App\Models\Investor;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class InvestorActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

    public function __construct(
        private readonly ChartOfAccountActions $chartOfAccountActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,

        ?string $search,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Investor::with(self::LIST_EAGER_LOADS)->select('investors.*')
            ->where('investors.company_id', $companyId)
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
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('investors.code', 'like', '%'.$search.'%')
                            ->orWhere('investors.name', 'like', '%'.$search.'%')
                            ->orWhere('investors.remarks', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('investors.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(investors.id, '.$includeId.') desc');
        }
        $query->orderBy('investors.name', 'asc');

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

    public function read(Investor $investor): Investor
    {
        return $investor->load(self::LIST_EAGER_LOADS);
    }

    public function create(InvestorCreateDTO $data): Investor
    {
        $timer_start = microtime(true);

        try {
            $investor = new Investor();
            $investor->company_id = $data->companyId;
            $investor->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $investor->name = $data->name;
            $investor->remarks = $data->remarks;
            $investor->save();

            $openingCapitalParentChartOfAccount = $investor->company->equityCapitalOpeningCapitalChartOfAccount;
            if (! $openingCapitalParentChartOfAccount) {
                throw new InvalidArgumentException('Opening capital chart of account parent must exist in company.');
            }

            $openingCapitalChartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $investor->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $openingCapitalParentChartOfAccount->id,
                sourceType: Investor::class,
                sourceId: $investor->id,
                code: $openingCapitalParentChartOfAccount->code.'.'.$investor->code,
                name: $investor->name,
                normalBalance: 'credit',
                isGroup: false,
                isActive: true,
                remarks: $investor->remarks,
            );
            $this->chartOfAccountActions->create($openingCapitalChartOfAccountDTO);

            $additionalCapitalParentChartOfAccount = $investor->company->equityCapitalAdditionalCapitalChartOfAccount;
            if (! $additionalCapitalParentChartOfAccount) {
                throw new InvalidArgumentException('Additional capital chart of account parent must exist in company.');
            }

            $additionalCapitalChartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $investor->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $additionalCapitalParentChartOfAccount->id,
                sourceType: Investor::class,
                sourceId: $investor->id,
                code: $additionalCapitalParentChartOfAccount->code.'.'.$investor->code,
                name: $investor->name,
                normalBalance: 'credit',
                isGroup: false,
                isActive: true,
                remarks: $investor->remarks,
            );
            $this->chartOfAccountActions->create($additionalCapitalChartOfAccountDTO);

            $drawingParentChartOfAccount = $investor->company->equityCapitalDrawingChartOfAccount;
            if (! $drawingParentChartOfAccount) {
                throw new InvalidArgumentException('Drawing chart of account parent must exist in company.');
            }

            $drawingChartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $investor->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $drawingParentChartOfAccount->id,
                sourceType: Investor::class,
                sourceId: $investor->id,
                code: $drawingParentChartOfAccount->code.'.'.$investor->code,
                name: $investor->name,
                normalBalance: 'debit',
                isGroup: false,
                isActive: true,
                remarks: $investor->remarks,
            );
            $this->chartOfAccountActions->create($drawingChartOfAccountDTO);

            $this->flushCache();

            return $investor;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Investor $investor, InvestorUpdateDTO $data): Investor
    {
        $timer_start = microtime(true);

        try {
            $investor->code = $this->generateUniqueCode($investor->company_id, $data->code, $investor->id);
            $investor->name = $data->name;
            $investor->remarks = $data->remarks;
            $investor->save();

            $openingCapitalParentChartOfAccount = $investor->company->equityCapitalOpeningCapitalChartOfAccount;
            if (! $openingCapitalParentChartOfAccount) {
                throw new InvalidArgumentException('Opening capital chart of account parent must exist in company.');
            }

            $openingCapitalChartOfAccount = $investor->openingCapitalChartOfAccount;
            if ($openingCapitalChartOfAccount) {
                $openingCapitalChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $openingCapitalParentChartOfAccount->id,
                    code: $openingCapitalParentChartOfAccount->code.'.'.$investor->code,
                    name: $investor->name,
                    normalBalance: 'credit',
                    isGroup: false,
                    isActive: true,
                    remarks: $investor->remarks,
                );
                $openingCapitalChartOfAccount = $this->chartOfAccountActions->update($openingCapitalChartOfAccount, $openingCapitalChartOfAccountDTO);
            } else {
                $openingCapitalChartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $investor->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $openingCapitalParentChartOfAccount->id,
                    sourceType: Investor::class,
                    sourceId: $investor->id,
                    code: $openingCapitalParentChartOfAccount->code.'.'.$investor->code,
                    name: $investor->name,
                    normalBalance: 'credit',
                    isGroup: false,
                    isActive: true,
                    remarks: $investor->remarks,
                );
                $openingCapitalChartOfAccount = $this->chartOfAccountActions->create($openingCapitalChartOfAccountDTO);
            }

            $additionalCapitalParentChartOfAccount = $investor->company->equityCapitalAdditionalCapitalChartOfAccount;
            if (! $additionalCapitalParentChartOfAccount) {
                throw new InvalidArgumentException('Additional capital chart of account parent must exist in company.');
            }

            $additionalCapitalChartOfAccount = $investor->additionalCapitalChartOfAccount;
            if ($additionalCapitalChartOfAccount) {
                $additionalCapitalChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $additionalCapitalParentChartOfAccount->id,
                    code: $additionalCapitalParentChartOfAccount->code.'.'.$investor->code,
                    name: $investor->name,
                    normalBalance: 'credit',
                    isGroup: false,
                    isActive: true,
                    remarks: $investor->remarks,
                );
                $additionalCapitalChartOfAccount = $this->chartOfAccountActions->update($additionalCapitalChartOfAccount, $additionalCapitalChartOfAccountDTO);
            } else {
                $additionalCapitalChartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $investor->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $additionalCapitalParentChartOfAccount->id,
                    sourceType: Investor::class,
                    sourceId: $investor->id,
                    code: $additionalCapitalParentChartOfAccount->code.'.'.$investor->code,
                    name: $investor->name,
                    normalBalance: 'credit',
                    isGroup: false,
                    isActive: true,
                    remarks: $investor->remarks,
                );
                $additionalCapitalChartOfAccount = $this->chartOfAccountActions->create($additionalCapitalChartOfAccountDTO);
            }

            $drawingParentChartOfAccount = $investor->company->equityCapitalDrawingChartOfAccount;
            if (! $drawingParentChartOfAccount) {
                throw new InvalidArgumentException('Drawing chart of account parent must exist in company.');
            }

            $drawingChartOfAccount = $investor->drawingChartOfAccount;
            if ($drawingChartOfAccount) {
                $drawingChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $drawingParentChartOfAccount->id,
                    code: $drawingParentChartOfAccount->code.'.'.$investor->code,
                    name: $investor->name,
                    normalBalance: 'debit',
                    isGroup: false,
                    isActive: true,
                    remarks: $investor->remarks,
                );
                $drawingChartOfAccount = $this->chartOfAccountActions->update($drawingChartOfAccount, $drawingChartOfAccountDTO);
            } else {
                $drawingChartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $investor->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $drawingParentChartOfAccount->id,
                    sourceType: Investor::class,
                    sourceId: $investor->id,
                    code: $drawingParentChartOfAccount->code.'.'.$investor->code,
                    name: $investor->name,
                    normalBalance: 'debit',
                    isGroup: false,
                    isActive: true,
                    remarks: $investor->remarks,
                );
                $drawingChartOfAccount = $this->chartOfAccountActions->create($drawingChartOfAccountDTO);
            }

            $usedChartOfAccountIds = [
                $openingCapitalChartOfAccount->id,
                $additionalCapitalChartOfAccount->id,
                $drawingChartOfAccount->id,
            ];

            foreach ($investor->chartOfAccounts as $chartOfAccount) {
                if (! in_array($chartOfAccount->id, $usedChartOfAccountIds, true)) {
                    $this->chartOfAccountActions->delete($chartOfAccount);
                }
            }

            $this->flushCache();

            return $investor->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Investor $investor): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            foreach ($investor->chartOfAccounts()->get() as $chartOfAccount) {
                $this->chartOfAccountActions->delete($chartOfAccount);
            }

            $retval = $investor->delete();

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
        if ($code != config('dcslab.KEYWORDS.AUTO')) {
            return $code;
        }

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->investors()->withTrashed()->count() + 1 + $tryCount;
            $code = 'INV'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->investors()->count() == 0) {
            return true;
        }

        $query = $company->investors()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('investors.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->investors()->count() == 0) {
            return true;
        }

        $query = $company->investors()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('investors.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
