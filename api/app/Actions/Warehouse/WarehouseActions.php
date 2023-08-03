<?php

namespace App\Actions\Warehouse;

use App\Actions\Randomizer\RandomizerActions;
use App\Models\Warehouse;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class WarehouseActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $warehouseArr): Warehouse
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $warehouse = new Warehouse();
            $warehouse->company_id = $warehouseArr['company_id'];
            $warehouse->branch_id = $warehouseArr['branch_id'];
            $warehouse->code = $warehouseArr['code'];
            $warehouse->name = $warehouseArr['name'];
            $warehouse->address = $warehouseArr['address'];
            $warehouse->city = $warehouseArr['city'];
            $warehouse->contact = $warehouseArr['contact'];
            $warehouse->remarks = $warehouseArr['remarks'];
            $warehouse->status = $warehouseArr['status'];

            $warehouse->save();

            DB::commit();

            $this->flushCache();

            return $warehouse;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'readAny_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (! $companyId) {
                return null;
            }

            $warehouse = count($with) != 0 ? Warehouse::with($with) : Warehouse::with('company', 'branch');
            $warehouse = $warehouse->whereCompanyId($companyId);

            if ($withTrashed) {
                $warehouse = $warehouse->withTrashed();
            }

            if (empty($search)) {
                $warehouse = $warehouse->latest();
            } else {
                $warehouse = $warehouse->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $warehouse->paginate(
                    perPage: $perPage,
                    page: $page
                );
            } else {
                $result = $warehouse->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function read(Warehouse $warehouse): Warehouse
    {
        return $warehouse->first();
    }

    public function update(
        Warehouse $warehouse,
        array $warehouseArr
    ): Warehouse {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $warehouse->update([
                'code' => $warehouseArr['code'],
                'name' => $warehouseArr['name'],
                'address' => $warehouseArr['address'],
                'city' => $warehouseArr['city'],
                'contact' => $warehouseArr['contact'],
                'remarks' => $warehouseArr['remarks'],
                'status' => $warehouseArr['status'],
            ]);

            DB::commit();

            $this->flushCache();

            return $warehouse->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Warehouse $warehouse): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $warehouse->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = app(RandomizerActions::class);
        $code = $rand->generateAlpha().$rand->generateNumeric();

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Warehouse::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
