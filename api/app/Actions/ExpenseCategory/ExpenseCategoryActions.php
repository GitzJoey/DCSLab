<?php

namespace App\Actions\ExpenseCategory;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ChartOfAccountUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExpenseCategoryCreateDTO;
use App\DTOs\ExpenseCategoryUpdateDTO;
use App\Models\ExpenseCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class ExpenseCategoryActions
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

    public function __construct(
        private readonly ChartOfAccountActions $chartOfAccountActions,
    ) {
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

        $query = ExpenseCategory::with($eagerLoads)
            ->select('expense_categories.*')
            ->whereCompanyId('expense_categories', $companyId)
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
                        $query->where('expense_categories.code', 'like', '%'.$search.'%')
                            ->orWhere('expense_categories.name', 'like', '%'.$search.'%');
                    });
                }

                if ($parentId) {
                    $query->where('expense_categories.parent_id', $parentId);
                }

                if (! is_null($hasParent)) {
                    if ($hasParent) {
                        $query->whereNotNull('expense_categories.parent_id');
                    } else {
                        $query->whereNull('expense_categories.parent_id');
                    }
                }

                if (! is_null($hasChildren)) {
                    $hasChildren
                        ? $query->whereHas('children')
                        : $query->whereDoesntHave('children');
                }
            });

            if ($includeId) {
                $query->orWhere('expense_categories.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(expense_categories.id, '.$includeId.') desc');
        }
        $query->orderBy('expense_categories.sequence', 'asc')
            ->orderBy('expense_categories.id', 'asc');

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

                $cacheKey = 'readAny_expense_category_'.implode('-', $cacheParams);

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

    public function read(ExpenseCategory $expenseCategory): ExpenseCategory
    {
        return $expenseCategory->load(self::TREE_LIST_EAGER_LOADS);
    }

    public function hasChildren(ExpenseCategory $expenseCategory): bool
    {
        return $expenseCategory->children()->exists();
    }

    public function create(ExpenseCategoryCreateDTO $data): ExpenseCategory
    {
        $timer_start = microtime(true);

        try {
            $expenseCategory = new ExpenseCategory();
            $expenseCategory->company_id = $data->companyId;
            $expenseCategory->parent_id = $data->parentId;
            $expenseCategory->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $expenseCategory->name = $data->name;
            $expenseCategory->sequence = $data->sequence;
            $expenseCategory->save();

            if ($expenseCategory->parent_id) {
                $parentChartOfAccount = $expenseCategory->parent?->chartOfAccount;
                if (! $parentChartOfAccount) {
                    throw new InvalidArgumentException('Expense category parent chart of account must exist in company.');
                }
            } else {
                $parentChartOfAccount = $expenseCategory->company->expenseRootChartOfAccount;
                if (! $parentChartOfAccount) {
                    throw new InvalidArgumentException('Expense category chart of account parent must exist in company.');
                }
            }

            $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $expenseCategory->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $parentChartOfAccount->id,
                sourceType: ExpenseCategory::class,
                sourceId: $expenseCategory->id,
                code: $parentChartOfAccount->code.'.'.$expenseCategory->code,
                name: $expenseCategory->name,
                normalBalance: 'debit',
                isGroup: false,
                isActive: true,
                remarks: null,
            );
            $this->chartOfAccountActions->create($chartOfAccountDTO);

            if ($expenseCategory->parent_id) {
                $parentExpenseCategoryChartOfAccount = $expenseCategory->parent?->chartOfAccount;
                if ($parentExpenseCategoryChartOfAccount && ! $parentExpenseCategoryChartOfAccount->is_group) {
                    $parentExpenseCategoryChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                        parentId: $parentExpenseCategoryChartOfAccount->parent_id,
                        code: $parentExpenseCategoryChartOfAccount->code,
                        name: $parentExpenseCategoryChartOfAccount->name,
                        normalBalance: 'debit',
                        isGroup: true,
                        isActive: $parentExpenseCategoryChartOfAccount->is_active,
                        remarks: $parentExpenseCategoryChartOfAccount->remarks,
                    );
                    $this->chartOfAccountActions->update($parentExpenseCategoryChartOfAccount, $parentExpenseCategoryChartOfAccountDTO);
                }
            }

            $this->flushCache();

            return $expenseCategory;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(ExpenseCategory $expenseCategory, ExpenseCategoryUpdateDTO $data): ExpenseCategory
    {
        $timer_start = microtime(true);

        try {
            $expenseCategory->code = $this->generateUniqueCode(
                $expenseCategory->company_id,
                $data->code,
                $expenseCategory->id,
            );
            $expenseCategory->name = $data->name;
            $expenseCategory->sequence = $data->sequence;
            $expenseCategory->save();

            if ($expenseCategory->parent_id) {
                $parentChartOfAccount = $expenseCategory->parent?->chartOfAccount;
                if (! $parentChartOfAccount) {
                    throw new InvalidArgumentException('Expense category parent chart of account must exist in company.');
                }
            } else {
                $parentChartOfAccount = $expenseCategory->company->expenseRootChartOfAccount;
                if (! $parentChartOfAccount) {
                    throw new InvalidArgumentException('Expense category chart of account parent must exist in company.');
                }
            }

            $chartOfAccount = $expenseCategory->chartOfAccount;
            if ($chartOfAccount) {
                $chartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $parentChartOfAccount->id,
                    code: $parentChartOfAccount->code.'.'.$expenseCategory->code,
                    name: $expenseCategory->name,
                    normalBalance: 'debit',
                    isGroup: $expenseCategory->children()->exists(),
                    isActive: true,
                    remarks: null,
                );
                $chartOfAccount = $this->chartOfAccountActions->update($chartOfAccount, $chartOfAccountDTO);
            } else {
                $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $expenseCategory->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $parentChartOfAccount->id,
                    sourceType: ExpenseCategory::class,
                    sourceId: $expenseCategory->id,
                    code: $parentChartOfAccount->code.'.'.$expenseCategory->code,
                    name: $expenseCategory->name,
                    normalBalance: 'debit',
                    isGroup: $expenseCategory->children()->exists(),
                    isActive: true,
                    remarks: null,
                );
                $chartOfAccount = $this->chartOfAccountActions->create($chartOfAccountDTO);
            }

            foreach ($expenseCategory->children as $childExpenseCategory) {
                $childChartOfAccount = $childExpenseCategory->chartOfAccount;
                if (! $childChartOfAccount) {
                    continue;
                }

                $childChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $chartOfAccount->id,
                    code: $chartOfAccount->code.'.'.$childExpenseCategory->code,
                    name: $childExpenseCategory->name,
                    normalBalance: 'debit',
                    isGroup: $childExpenseCategory->children()->exists(),
                    isActive: true,
                    remarks: null,
                );
                $this->chartOfAccountActions->update($childChartOfAccount, $childChartOfAccountDTO);
            }

            $this->flushCache();

            return $expenseCategory->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(ExpenseCategory $expenseCategory): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $parentExpenseCategory = $expenseCategory->parent;
            $chartOfAccount = $expenseCategory->chartOfAccount;
            if ($chartOfAccount) {
                $this->chartOfAccountActions->delete($chartOfAccount);
            }

            $retval = $expenseCategory->delete();

            if ($parentExpenseCategory && ! $parentExpenseCategory->children()->exists()) {
                $parentExpenseCategoryChartOfAccount = $parentExpenseCategory->chartOfAccount;
                if ($parentExpenseCategoryChartOfAccount) {
                    $parentExpenseCategoryChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                        parentId: $parentExpenseCategoryChartOfAccount->parent_id,
                        code: $parentExpenseCategoryChartOfAccount->code,
                        name: $parentExpenseCategoryChartOfAccount->name,
                        normalBalance: 'debit',
                        isGroup: false,
                        isActive: $parentExpenseCategoryChartOfAccount->is_active,
                        remarks: $parentExpenseCategoryChartOfAccount->remarks,
                    );
                    $this->chartOfAccountActions->update($parentExpenseCategoryChartOfAccount, $parentExpenseCategoryChartOfAccountDTO);
                }
            }

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
            $count = ExpenseCategory::withTrashed()
                ->where('company_id', $companyId)
                ->count() + 1 + $tryCount;
            $code = 'EC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $query = ExpenseCategory::where('company_id', $companyId)
            ->where('name', '=', $name);
        if ($exceptId) {
            $query->where('expense_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = ExpenseCategory::where('company_id', $companyId)
            ->where('code', '=', $code);
        if ($exceptId) {
            $query->where('expense_categories.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
