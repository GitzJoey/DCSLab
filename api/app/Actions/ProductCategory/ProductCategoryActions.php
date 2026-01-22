<?php

namespace App\Actions\ProductCategory;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ProductCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): ProductCategory
    {
        $timer_start = microtime(true);

        try {
            $productCategory = new ProductCategory();
            $productCategory->company_id = $data['company_id'];
            $productCategory->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $productCategory->name = $data['name'];
            $productCategory->type = $data['type'];
            $productCategory->save();

            $this->flushCache();

            return $productCategory;
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
        ?int $type,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = ProductCategory::with('company')->select('product_categories.*')
            ->whereCompanyId($companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $type, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search, $type) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                if ($type !== null) {
                    $query->where('product_categories.type', $type);
                }
            });

            if ($includeId) {
                $query->orWhere('product_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(product_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('product_categories.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $type ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_'.implode('-', $cacheParams);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheData;
                    }
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

    public function read(ProductCategory $productCategory): ProductCategory
    {
        return $productCategory->load('company');
    }

    public function update(ProductCategory $productCategory, array $data): ProductCategory
    {
        $timer_start = microtime(true);

        try {
            $productCategory->code = $this->generateUniqueCode($productCategory->company_id, $data['code'], $productCategory->id);
            $productCategory->name = $data['name'];
            $productCategory->type = $data['type'];
            $productCategory->save();

            $this->flushCache();

            return $productCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(ProductCategory $productCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $productCategory->delete();

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
            $count = $company->productCategories()->withTrashed()->count() + 1 + $tryCount;
            $code = 'PC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->productCategories()->count() == 0) {
            return true;
        }

        $query = $company->productCategories()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('product_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->productCategories()->count() == 0) {
            return true;
        }

        $query = $company->productCategories()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('product_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
