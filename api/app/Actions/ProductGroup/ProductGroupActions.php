<?php

namespace App\Actions\ProductGroup;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\ProductGroupCategory;
use App\Models\ProductGroup;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProductGroupActions
{
    use CacheHelper;
    use LoggerHelper;

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
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
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
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        int $companyId,
        ?int $category,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.$category.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (count($with) != 0) {
                $productGroup = ProductGroup::with($with)->whereCompanyId($companyId);
            } else {
                $productGroup = ProductGroup::whereCompanyId($companyId);
            }

            if ($category) {
                switch ($category) {
                    case ProductGroupCategory::PRODUCTS->value:
                        $productGroup = $productGroup->where('category', '=', ProductGroupCategory::PRODUCTS->value);
                        break;
                    case ProductGroupCategory::SERVICES->value:
                        $productGroup = $productGroup->where('category', '=', ProductGroupCategory::SERVICES->value);
                        break;
                }
            }

            if ($withTrashed) {
                $productGroup = $productGroup->withTrashed();
            }

            if (empty($search)) {
                $productGroup = $productGroup->latest();
            } else {
                $productGroup = $productGroup->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $productGroup->paginate(
                    perPage: $perPage,
                    page: $page
                );
            } else {
                $result = $productGroup->get();
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
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
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
        $result = ProductGroup::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function getProductGroupDDL(int $companyId, ?int $category): Collection 
    {
        $productGroup = ProductGroup::whereCompanyId($companyId);

        if ($category) {
            switch ($category) {
                case ProductGroupCategory::PRODUCTS->value:
                    $productGroup = $productGroup->where('category', '=', ProductGroupCategory::PRODUCTS->value);
                    break;
                case ProductGroupCategory::SERVICES->value:
                    $productGroup = $productGroup->where('category', '=', ProductGroupCategory::SERVICES->value);
                    break;
            }
        }

        return $productGroup->latest()->get();
    }
}
