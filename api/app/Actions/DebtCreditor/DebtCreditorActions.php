<?php

namespace App\Actions\DebtCreditor;

use App\DTOs\DebtCreditorCreateDTO;
use App\DTOs\DebtCreditorUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\DebtCreditor;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class DebtCreditorActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

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
        $query = DebtCreditor::with(self::LIST_EAGER_LOADS)->select('debt_creditors.*')
            ->where('debt_creditors.company_id', $companyId)
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
                        $query->where('debt_creditors.code', 'like', '%'.$search.'%')
                            ->orWhere('debt_creditors.name', 'like', '%'.$search.'%')
                            ->orWhere('debt_creditors.remarks', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('debt_creditors.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(debt_creditors.id, '.$includeId.') desc');
        }
        $query->orderBy('debt_creditors.name', 'asc');

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

                $cacheKey = 'readAny_debt_creditor_'.implode('-', $cacheParams);

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

    public function read(DebtCreditor $debtCreditor): DebtCreditor
    {
        return $debtCreditor->load(self::LIST_EAGER_LOADS);
    }

    public function create(DebtCreditorCreateDTO $data): DebtCreditor
    {
        $timer_start = microtime(true);

        try {
            $debtCreditor = new DebtCreditor();
            $debtCreditor->company_id = $data->companyId;
            $debtCreditor->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $debtCreditor->name = $data->name;
            $debtCreditor->remarks = $data->remarks;
            $debtCreditor->save();

            $this->flushCache();

            return $debtCreditor;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(DebtCreditor $debtCreditor, DebtCreditorUpdateDTO $data): DebtCreditor
    {
        $timer_start = microtime(true);

        try {
            $debtCreditor->code = $this->generateUniqueCode($debtCreditor->company_id, $data->code, $debtCreditor->id);
            $debtCreditor->name = $data->name;
            $debtCreditor->remarks = $data->remarks;
            $debtCreditor->save();

            $this->flushCache();

            return $debtCreditor->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(DebtCreditor $debtCreditor): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $debtCreditor->delete();

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
            $count = $company->debt_creditors()->withTrashed()->count() + 1 + $tryCount;
            $code = 'DCR'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->debt_creditors()->count() == 0) {
            return true;
        }

        $query = $company->debt_creditors()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('debt_creditors.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->debt_creditors()->count() == 0) {
            return true;
        }

        $query = $company->debt_creditors()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('debt_creditors.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
