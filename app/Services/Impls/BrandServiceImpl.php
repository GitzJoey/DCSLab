<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Services\BrandService;

use App\Models\Brand;
use App\Traits\CacheHelper;

class BrandServiceImpl implements BrandService
{
    use CacheHelper;

    public function __construct()
    {
        
    }

    public function create(int $company_id, string $code, string $name): Brand
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $productbrand = new Brand();
            $productbrand->company_id = $company_id;
            $productbrand->code = $code;
            $productbrand->name = $name;

            $productbrand->save();

            DB::commit();

            $this->flushCache();

            return $productbrand;
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
        int $page, 
        ?int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection|null
    {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'read_'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
            $cacheResult = $this->readFromCache($cacheKey);

            if (!is_null($cacheResult)) return $cacheResult;
        }

        $result = null;

        $timer_start = microtime(true);

        try {
            if (empty($search)) {
                $pb = Brand::whereCompanyId($companyId)->latest();
            } else {
                $pb = Brand::whereCompanyId($companyId)->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $pb->paginate($perPage);
            } else {
                return $pb->get();
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

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return Brand::find($value);
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(int $id, int $company_id, string $code, string $name): Brand
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productbrand = Brand::find($id);

            $productbrand->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
            ]);

            DB::commit();

            $this->flushCache();

            return $productbrand->refresh();
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
            $productbrand = Brand::find($id);
            if ($productbrand) {
                $retval = $productbrand->delete();
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
        $result = Brand::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
