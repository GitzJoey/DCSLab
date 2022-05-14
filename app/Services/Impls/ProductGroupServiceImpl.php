<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ProductGroupCategory;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ProductGroupService;
use App\Models\ProductGroup;
use App\Traits\CacheHelper;
use Illuminate\Support\Facades\Cache;

class ProductGroupServiceImpl implements ProductGroupService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $category
    ): ?ProductGroup
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productGroup = new ProductGroup();
            $productGroup->company_id = $company_id;
            $productGroup->code = $code;
            $productGroup->name = $name;
            $productGroup->category = $category;

            $productGroup->save();

            DB::commit();

            $this->flushCache();

            return $productGroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function readBy(
        string $key, 
        string $value
    )
    {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return ProductGroup::find($value);
                default:
                    return null;
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

    public function read(
        int $companyId, 
        ?string $category = null, 
        string $search = '', 
        bool $paginate = true, 
        int $page, 
        ?int $perPage = 10, 
        bool $useCache = true
    )
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

            $productGroup = ProductGroup::whereCompanyId($companyId);

            if (!empty($category)) {
                $productGroup = $productGroup->where('category', '=', ProductGroupCategory::PRODUCTS->value);
            } 
    
            if (empty($search)) {
                $productGroup = $productGroup->latest();
            } else {
                $productGroup = $productGroup->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $productGroup->paginate($perPage);
            } else {
                return $productGroup->get();
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
        string $code,
        string $name,
        string $category
    ): ?ProductGroup
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productgroup = ProductGroup::find($id);
    
            $productgroup->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
            ]);

            DB::commit();

            $this->flushCache();

            return $productgroup->refresh();
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
            $productGroup = ProductGroup::find($id);
            
            if ($productGroup) {
                $retval = $productGroup->delete();
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
        $result = ProductGroup::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}