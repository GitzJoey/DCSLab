<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ProductGroupCategory;
use App\Models\ProductGroup;
use App\Services\ProductGroupService;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductGroupServiceImpl implements ProductGroupService
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function create(
        array $productGroupArr
    ): ProductGroup {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productGroup = new ProductGroup();
            $productGroup->company_id = $productGroupArr['company_id'];
            $productGroup->code = $productGroupArr['code'];
            $productGroup->name = $productGroupArr['name'];
            $productGroup->category = $productGroupArr['category'];

            $productGroup->save();

            DB::commit();

            $this->flushCache();

            return $productGroup;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function readBy(
        string $key,
        string $value
    ) {
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $companyId,
        int $category,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.$category.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $productGroup = ProductGroup::whereCompanyId($companyId);

            if ($category == ProductGroupCategory::PRODUCTS->value) {
                $productGroup = $productGroup->where('category', '<>', ProductGroupCategory::SERVICES->value);
            } elseif ($category == ProductGroupCategory::SERVICES->value) {
                $productGroup = $productGroup->where('category', '<>', ProductGroupCategory::PRODUCTS->value);
            } elseif ($category == ProductGroupCategory::PRODUCTS_AND_SERVICES->value) {
            } else {
                return null;
            }

            if (empty($search)) {
                $productGroup = $productGroup->latest();
            } else {
                $productGroup = $productGroup->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $productGroup->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $productGroup->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    public function read(ProductGroup $productgroup): ProductGroup
    {
        return $productgroup->first();
    }

    public function update(
        ProductGroup $productgroup,
        array $productGroupArr
    ): ProductGroup {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productgroup->update([
                'code' => $productGroupArr['code'],
                'name' => $productGroupArr['name'],
                'category' => $productGroupArr['category'],
            ]);

            DB::commit();

            $this->flushCache();

            return $productgroup->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(ProductGroup $productgroup): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $productgroup->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
        $result = ProductGroup::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
