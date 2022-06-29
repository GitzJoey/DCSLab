<?php

namespace App\Services\Impls;

use App\Services\WarehouseService;
use App\Models\Warehouse;

use Exception;
use App\Actions\RandomGenerator;
use App\Traits\CacheHelper;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WarehouseServiceImpl implements WarehouseService
{
    use CacheHelper;

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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection
    {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            if (!$companyId) return null;

            $warehouse = Warehouse::with('company', 'branch')
                        ->whereCompanyId($companyId);
    
            if (empty($search)) {
                $warehouse = $warehouse->latest();
            } else {
                $warehouse = $warehouse->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                return $warehouse->paginate($perPage);
            } else {
                return $warehouse->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function read(Warehouse $warehouse): Warehouse
    {
        return $warehouse->first();

    }

    public function update(
        Warehouse $warehouse,
        array $warehouseArr
    ): Warehouse
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $warehouse->update([
                'company_id' => $warehouseArr['company_id'],
                'branch_id' => $warehouseArr['branch_id'],
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Warehouse::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}