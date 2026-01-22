<?php

namespace App\Actions\Warehouse;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Warehouse;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class WarehouseActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Warehouse
    {
        $timer_start = microtime(true);

        try {
            $warehouse = new Warehouse();
            $warehouse->company_id = $data['company_id'];
            $warehouse->branch_id = $data['branch_id'];
            $warehouse->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $warehouse->name = $data['name'];
            $warehouse->address = $data['address'];
            $warehouse->city = $data['city'];
            $warehouse->contact = $data['contact'];
            $warehouse->remarks = $data['remarks'];
            $warehouse->status = $data['status'];
            $warehouse->save();

            $this->flushCache();

            return $warehouse;
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
        ?int $branchId,
        ?int $status,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Warehouse::with('company', 'branch')->select('warehouses.*')
            ->whereCompanyId($companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $status, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search, $branchId, $status) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->search($search);
                }

                if ($branchId) {
                    $query->where('warehouses.branch_id', $branchId);
                }

                if ($status !== null) {
                    $query->where('warehouses.status', '=', $status);
                }
            });

            if ($includeId) {
                $query->orWhere('warehouses.id', $includeId);
            }
        });

        if ($includeId) $query->orderByRaw('FIELD(warehouses.id, '.$includeId.') desc');
        $query->orderBy('warehouses.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $status ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_'.implode('-', $cacheParams);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) return $cacheData;
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

    public function read(Warehouse $warehouse): Warehouse
    {
        return $warehouse->load('company', 'branch');
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $timer_start = microtime(true);

        try {
            $warehouse->code = $this->generateUniqueCode($warehouse->company_id, $data['code'], $warehouse->id);
            $warehouse->name = $data['name'];
            $warehouse->address = $data['address'];
            $warehouse->city = $data['city'];
            $warehouse->contact = $data['contact'];
            $warehouse->remarks = $data['remarks'];
            $warehouse->status = $data['status'];
            $warehouse->save();

            $this->flushCache();

            return $warehouse->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Warehouse $warehouse): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $warehouse->delete();

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
            $count = $company->warehouses()->withTrashed()->count() + 1 + $tryCount;
            $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->warehouses()->count() == 0) return true;

        $query = $company->warehouses()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('warehouses.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->warehouses()->count() == 0) return true;

        $query = $company->warehouses()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('warehouses.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
