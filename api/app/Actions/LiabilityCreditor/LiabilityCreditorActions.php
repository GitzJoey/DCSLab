<?php

namespace App\Actions\LiabilityCreditor;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\LiabilityCreditor;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class LiabilityCreditorActions
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
        $query = LiabilityCreditor::with(self::LIST_EAGER_LOADS)->select('liability_creditors.*')
            ->where('liability_creditors.company_id', $companyId)
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
                        $query->where('liability_creditors.code', 'like', '%'.$search.'%')
                            ->orWhere('liability_creditors.name', 'like', '%'.$search.'%')
                            ->orWhere('liability_creditors.remarks', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('liability_creditors.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(liability_creditors.id, '.$includeId.') desc');
        }
        $query->orderBy('liability_creditors.name', 'asc');

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

    public function read(LiabilityCreditor $liabilityCreditor): LiabilityCreditor
    {
        return $liabilityCreditor->load(self::LIST_EAGER_LOADS);
    }

    public function create(array $data): LiabilityCreditor
    {
        $timer_start = microtime(true);

        try {
            $liabilityCreditor = new LiabilityCreditor();
            $liabilityCreditor->company_id = $data['company_id'];
            $liabilityCreditor->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $liabilityCreditor->name = $data['name'];
            $liabilityCreditor->remarks = $data['remarks'];
            $liabilityCreditor->save();

            $this->flushCache();

            return $liabilityCreditor;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(LiabilityCreditor $liabilityCreditor, array $data): LiabilityCreditor
    {
        $timer_start = microtime(true);

        try {
            $liabilityCreditor->code = $this->generateUniqueCode($liabilityCreditor->company_id, $data['code'], $liabilityCreditor->id);
            $liabilityCreditor->name = $data['name'];
            $liabilityCreditor->remarks = $data['remarks'];
            $liabilityCreditor->save();

            $this->flushCache();

            return $liabilityCreditor->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(LiabilityCreditor $liabilityCreditor): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $liabilityCreditor->delete();

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
            $count = $company->liability_creditors()->withTrashed()->count() + 1 + $tryCount;
            $code = 'LIC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->liability_creditors()->count() == 0) {
            return true;
        }

        $query = $company->liability_creditors()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('liability_creditors.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->liability_creditors()->count() == 0) {
            return true;
        }

        $query = $company->liability_creditors()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('liability_creditors.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
