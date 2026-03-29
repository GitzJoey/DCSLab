<?php

namespace App\Actions\RepToPascalThis;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\RepToPascalThis;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class RepToPascalThisActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,

        ?string $search,

        ?ExecuteDTO $execute
    ) {
        $query = RepToPascalThis::select('RepToSnakePluralsThis.*')
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'RepToSnakePluralsThis.company_id')
            ->whereCompanyId('RepToSnakePluralsThis', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('RepToSnakePluralsThis.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_RepToSnakeThis_'.implode('_', $cacheParams);

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

    public function read(RepToPascalThis $RepToCamelThis): RepToPascalThis
    {
        return $RepToCamelThis->load(['company']);
    }

    public function create(array $data): RepToPascalThis
    {
        $timer_start = microtime(true);

        try {
            $RepToCamelThis = new RepToPascalThis();
            $RepToCamelThis->company_id = $data['company_id'];
            $RepToCamelThis->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $RepToCamelThis->remarks = $data['remarks'];
            $RepToCamelThis->save();

            $this->flushCache();

            return $RepToCamelThis;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(RepToPascalThis $RepToCamelThis, array $data): RepToPascalThis
    {
        $timer_start = microtime(true);

        try {
            $RepToCamelThis->code = $this->generateUniqueCode($RepToCamelThis->company_id, $data['code'], $RepToCamelThis->id);
            $RepToCamelThis->remarks = $data['remarks'];
            $RepToCamelThis->save();

            $this->flushCache();

            return $RepToCamelThis->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(RepToPascalThis $RepToCamelThis): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $RepToCamelThis->delete();

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
                $count = $company->RepToCamelPluralsThis()->withTrashed()->count() + 1 + $tryCount;
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
        $result = RepToPascalThis::whereCompanyId('RepToSnakePluralsThis', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
