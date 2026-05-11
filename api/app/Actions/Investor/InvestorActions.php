<?php

namespace App\Actions\Investor;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Investor;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

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

    public function create(array $data): Investor
    {
        $timer_start = microtime(true);

        try {
            $investor = new Investor();
            $investor->company_id = $data['company_id'];
            $investor->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $investor->name = $data['name'];
            $investor->remarks = $data['remarks'];
            $investor->save();

            $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $investor->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $investor->company->equityRootChartOfAccount->id,
                sourceType: Investor::class,
                sourceId: $investor->id,
                code: $investor->company->equityRootChartOfAccount->code.$investor->code,
                name: $investor->name,
                normalBalance: 'credit',
                isGroup: false,
                isActive: true,
                remarks: $investor->remarks,
            );
            $this->chartOfAccountActions->create($chartOfAccountDTO);

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

    public function update(Investor $investor, array $data): Investor
    {
        $timer_start = microtime(true);

        try {
            $investor->code = $this->generateUniqueCode($investor->company_id, $data['code'], $investor->id);
            $investor->name = $data['name'];
            $investor->remarks = $data['remarks'];
            $investor->save();

            $chartOfAccount = $investor->chartOfAccount;
            $this->chartOfAccountActions->update($chartOfAccount, [
                'parent_id' => $chartOfAccount->parent_id,
                'code' => $investor->code,
                'name' => $investor->name,
                'account_type' => $chartOfAccount->account_type,
                'normal_balance' => $chartOfAccount->normal_balance,
                'is_group' => $chartOfAccount->is_group,
                'is_active' => $chartOfAccount->is_active,
                'remarks' => $investor->remarks,
            ]);

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
            $chartOfAccount = $investor->chartOfAccount;
            if ($chartOfAccount) $this->chartOfAccountActions->delete($chartOfAccount);

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
