<?php

namespace App\Actions\StockAdjustmentCategory;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\StockAdjustmentCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId = null): bool
    {
        $query = StockAdjustmentCategory::whereCompanyId($companyId)->whereName($name);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function create(array $data): StockAdjustmentCategory
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentCategory = new StockAdjustmentCategory();
            $stockAdjustmentCategory->company_id = $data['company_id'];
            $stockAdjustmentCategory->code = $this->generateUniqueCode(
                $data['company_id'],
                $data['code'],
                null,
            );
            $stockAdjustmentCategory->name = $data['name'];
            $stockAdjustmentCategory->save();

            $this->flushCache();

            return $stockAdjustmentCategory;
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
        ?string $search,
        int $companyId,
        ?int $includeId,
        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentCategory::with(['company'])->select('stock_adjustment_categories.*')
            ->where('stock_adjustment_categories.company_id', $companyId)
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
                $query->orWhere('stock_adjustment_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(stock_adjustment_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('stock_adjustment_categories.name', 'asc');

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

    public function read(StockAdjustmentCategory $stockAdjustmentCategory): StockAdjustmentCategory
    {
        return $stockAdjustmentCategory->load('company');
    }

    public function update(StockAdjustmentCategory $stockAdjustmentCategory, array $data): StockAdjustmentCategory
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentCategory->code = $this->generateUniqueCode(
                $stockAdjustmentCategory->company_id,
                $data['code'],
                $stockAdjustmentCategory->id,
            );
            $stockAdjustmentCategory->name = $data['name'];
            $stockAdjustmentCategory->save();

            $this->flushCache();

            return $stockAdjustmentCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentCategory $stockAdjustmentCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $stockAdjustmentCategory->delete();

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
            $count = $company->stockAdjustmentCategories()
                ->withTrashed()
                ->count() + 1 + $tryCount;
            $code = 'SAC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->stockAdjustmentCategories()->count() == 0) {
            return true;
        }

        $query = $company->stockAdjustmentCategories()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('stock_adjustment_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
