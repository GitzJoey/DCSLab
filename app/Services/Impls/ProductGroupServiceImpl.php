<?php

namespace App\Services\Impls;

use Exception;

use App\Traits\CacheHelper;
use App\Models\ProductGroup;
use App\Actions\RandomGenerator;
use App\Enums\ProductCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use App\Services\ProductGroupService;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Pagination\Paginator;

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
        int $category
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

            return $productGroup;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
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
        int $page = 1, 
        ?int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection|null
    {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.$category.'-'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            $product_group = ProductGroup::whereCompanyId($companyId);
         
            if (!empty($category)) {
                if ($category == ProductCategory::PRODUCTS) {
                    $product_group = $product_group->where('category', '=', ProductCategory::PRODUCTS->value);
                } else if ($category == ProductCategory::SERVICES) {
                    $product_group = $product_group->where('category', '=', ProductCategory::SERVICES->value);
                } else if ($category == ProductCategory::PRODUCTS_AND_SERVICES) {
                    $product_group = $product_group->where('category', '=', ProductCategory::PRODUCTS_AND_SERVICES->value);
                }
            }
            
            if (empty($search)) {
                $product_group = $product_group->latest();
            } else {
                $product_group = $product_group->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                $result = $product_group->paginate($perPage);
            } else {
                $result = $product_group->get();
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
        int $category
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
                'category' => $category
            ]);

            DB::commit();

            $this->flushCache();

            return $productgroup->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
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