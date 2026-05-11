<?php

namespace App\Actions\IncomeCategory;

use App\DTOs\ExecuteDTO;
use App\DTOs\IncomeCategoryCreateDTO;
use App\DTOs\IncomeCategoryUpdateDTO;
use App\Models\IncomeCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class IncomeCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

    private const PAGINATED_LIST_EAGER_LOADS = [
        'company',
        'parent',
    ];

    private const TREE_LIST_EAGER_LOADS = [
        'company',
        'parent',
        'childrenRecursive',
    ];

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,

        ?int $parentId,
        ?bool $hasParent,
        ?bool $hasChildren,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $eagerLoads = $execute?->pagination
            ? self::PAGINATED_LIST_EAGER_LOADS
            : self::TREE_LIST_EAGER_LOADS;

        $query = IncomeCategory::with($eagerLoads)
            ->select('income_categories.*')
            ->whereCompanyId('income_categories', $companyId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $parentId,
            $hasParent,
            $hasChildren,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $parentId,
                $hasParent,
                $hasChildren,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('income_categories.code', 'like', '%'.$search.'%')
                            ->orWhere('income_categories.name', 'like', '%'.$search.'%');
                    });
                }

                if ($parentId) {
                    $query->where('income_categories.parent_id', $parentId);
                }

                if (! is_null($hasParent)) {
                    if ($hasParent) {
                        $query->whereNotNull('income_categories.parent_id');
                    } else {
                        $query->whereNull('income_categories.parent_id');
                    }
                }

                if (! is_null($hasChildren)) {
                    $hasChildren
                        ? $query->whereHas('children')
                        : $query->whereDoesntHave('children');
                }
            });

            if ($includeId) {
                $query->orWhere('income_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(income_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('income_categories.sequence', 'asc')
            ->orderBy('income_categories.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $parentId ?? '[null]',
                    is_null($hasParent) ? '[null]' : ($hasParent ? 'true' : 'false'),
                    is_null($hasChildren) ? '[null]' : ($hasChildren ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_income_category_'.implode('-', $cacheParams);

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

    public function read(IncomeCategory $incomeCategory): IncomeCategory
    {
        return $incomeCategory->load(self::TREE_LIST_EAGER_LOADS);
    }

    public function hasChildren(IncomeCategory $incomeCategory): bool
    {
        return $incomeCategory->children()->exists();
    }

    public function create(IncomeCategoryCreateDTO $data): IncomeCategory
    {
        $timer_start = microtime(true);

        try {
            $incomeCategory = new IncomeCategory();
            $incomeCategory->company_id = $data->companyId;
            $incomeCategory->parent_id = $data->parentId;
            $incomeCategory->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $incomeCategory->name = $data->name;
            $incomeCategory->sequence = $data->sequence;
            $incomeCategory->save();

            $this->flushCache();

            return $incomeCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(IncomeCategory $incomeCategory, IncomeCategoryUpdateDTO $data): IncomeCategory
    {
        $timer_start = microtime(true);

        try {
            $incomeCategory->code = $this->generateUniqueCode(
                $incomeCategory->company_id,
                $data->code,
                $incomeCategory->id,
            );
            $incomeCategory->name = $data->name;
            $incomeCategory->sequence = $data->sequence;
            $incomeCategory->save();

            $this->flushCache();

            return $incomeCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(IncomeCategory $incomeCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $incomeCategory->delete();

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

        $tryCount = 0;
        do {
            $count = IncomeCategory::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'EC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $query = IncomeCategory::where('company_id', $companyId)
            ->where('name', '=', $name);
        if ($exceptId) {
            $query->where('income_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = IncomeCategory::where('company_id', $companyId)
            ->where('code', '=', $code);
        if ($exceptId) {
            $query->where('income_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
