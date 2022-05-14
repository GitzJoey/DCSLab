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
    
    public function create(
        int $company_id,
        int $branch_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        int $status,
    ): ?Warehouse
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $warehouse = new Warehouse();
            $warehouse->company_id = $company_id;
            $warehouse->branch_id = $branch_id;
            $warehouse->code = $code;
            $warehouse->name = $name;
            $warehouse->address = $address;
            $warehouse->city = $city;
            $warehouse->contact = $contact;
            $warehouse->remarks = $remarks;
            $warehouse->status = $status;

            $warehouse->save();

            DB::commit();

            $this->flushCache();

            return $warehouse;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection|null
    {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            if (!$companyId) return null;

            $warehouse = Warehouse::with('company')
                        ->whereCompanyId($companyId);
    
            if (empty($search)) {
                $warehouse = $warehouse->latest();
            } else {
                $warehouse = $warehouse->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $warehouse->paginate($perPage);
            } else {
                return $warehouse->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function update(
        int $id,
        int $company_id,
        int $branch_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        int $status,
    ): ?Warehouse
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $warehouse = Warehouse::find($id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }
    
            $warehouse->update([
                'company_id' => $company_id,
                'branch_id' => $branch_id,
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'remarks' => $remarks,
                'status' => $status,
            ]);

            DB::commit();

            $this->flushCache();

            return $warehouse->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $warehouse = Warehouse::find($id);

            if ($warehouse) {
                $retval = $warehouse->delete();
            }

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return $retval;
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