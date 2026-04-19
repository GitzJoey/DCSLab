<?php

namespace App\Actions\PurchaseAdditionalCostCategory;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\PurchaseAdditionalCostCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseAdditionalCostCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,

        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseAdditionalCostCategory::with(self::LIST_EAGER_LOADS)
            ->select('purchase_additional_cost_categories.*')
            ->whereCompanyId('purchase_additional_cost_categories', $companyId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }
            });

            if ($includeId) {
                $query->orWhere('purchase_additional_cost_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(purchase_additional_cost_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('purchase_additional_cost_categories.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    empty($search) ? '[empty]' : $search,
                    $companyId,
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_purchase_additional_cost_category_'.implode('-', $cacheParams);

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

    public function read(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory): PurchaseAdditionalCostCategory
    {
        return $purchaseAdditionalCostCategory->load(self::LIST_EAGER_LOADS);
    }

    public function create(array $data): PurchaseAdditionalCostCategory
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCostCategory = new PurchaseAdditionalCostCategory();
            $purchaseAdditionalCostCategory->company_id = $data['company_id'];
            $purchaseAdditionalCostCategory->code = $this->generateUniqueCode(
                $data['company_id'],
                $data['code'],
                null,
            );
            $purchaseAdditionalCostCategory->name = $data['name'];
            $purchaseAdditionalCostCategory->save();

            $this->flushCache();

            return $purchaseAdditionalCostCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory, array $data): PurchaseAdditionalCostCategory
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCostCategory->code = $this->generateUniqueCode(
                $purchaseAdditionalCostCategory->company_id,
                $data['code'],
                $purchaseAdditionalCostCategory->id,
            );
            $purchaseAdditionalCostCategory->name = $data['name'];
            $purchaseAdditionalCostCategory->save();

            $this->flushCache();

            return $purchaseAdditionalCostCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory): bool
    {
        $timer_start = microtime(true);

        try {
            $retval = $purchaseAdditionalCostCategory->delete();

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
            $count = $company->purchaseAdditionalCostCategories()
                ->withTrashed()
                ->count() + 1 + $tryCount;
            $code = 'PACC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->purchaseAdditionalCostCategories()->count() == 0) {
            return true;
        }

        $query = $company->purchaseAdditionalCostCategories()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('purchase_additional_cost_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->purchaseAdditionalCostCategories()->count() == 0) {
            return true;
        }

        $query = $company->purchaseAdditionalCostCategories()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('purchase_additional_cost_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
