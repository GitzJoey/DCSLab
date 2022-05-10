<?php

namespace App\Services\Impls;

use App\Services\WarehouseService;
use App\Models\Warehouse;

use Exception;
use App\Actions\RandomGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WarehouseServiceImpl implements WarehouseService
{
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

            return $warehouse;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10
    )
    {
        $timer_start = microtime(true);

        try {
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
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function readFromCache($key)
    {
        try {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED')) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            if (!Cache::has($key)) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            return Cache::get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] Read Key'.$key);
        }
    }

    private function saveToCache($key, $val)
    {
        try {
            if (empty($key)) return;

            Cache::tags([auth()->user()->id, __METHOD__])->add($key, $val, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.ENV'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] Save Key'.$key);
        }
    }

    private function flushCache()
    {
        try {
            Cache::tags([auth()->user()->id, __METHOD__])->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] Cache Flushed for tags '.auth()->user()->id.', '.__METHOD__);
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

            return $warehouse->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $timer_start = microtime(true);

        try {
            $result = Warehouse::whereCompanyId($companyId)->where('code', '=' , $code);

            if($exceptId)
                $result = $result->where('id', '<>', $exceptId);
    
            return $result->count() == 0 ? true:false;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }
}