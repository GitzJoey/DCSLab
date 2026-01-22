<?php

namespace App\Actions\Unit;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Unit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class UnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Unit
    {
        $timer_start = microtime(true);

        try {
            $unit = new Unit();
            $unit->company_id = $data['company_id'];
            $unit->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $unit->name = $data['name'];
            $unit->description = $data['description'];
            $unit->type = $data['type'];
            $unit->save();

            $this->flushCache();

            return $unit;
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

        ?string $search,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Unit::with('company')->select('units.*')
            ->where('units.company_id', $companyId)
            ->withTrashed();

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
                $query->orWhere('units.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(units.id, '.$includeId.') desc');
        }
        $query->orderBy('units.name', 'asc');

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

    public function read(Unit $unit): Unit
    {
        return $unit->load('company');
    }

    public function update(Unit $unit, array $data): Unit
    {
        $timer_start = microtime(true);

        try {
            $unit->code = $this->generateUniqueCode($unit->company_id, $data['code'], $unit->id);
            $unit->name = $data['name'];
            $unit->description = $data['description'];
            $unit->type = $data['type'];
            $unit->save();

            $this->flushCache();

            return $unit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Unit $unit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $unit->delete();

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
            $count = $company->units()->withTrashed()->count() + 1 + $tryCount;
            $code = 'UNT'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->units()->count() == 0) {
            return true;
        }

        $query = $company->units()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('units.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->units()->count() == 0) {
            return true;
        }

        $query = $company->units()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('units.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
