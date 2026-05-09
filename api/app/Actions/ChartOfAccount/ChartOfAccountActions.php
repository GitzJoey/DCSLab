<?php

namespace App\Actions\ChartOfAccount;

use App\DTOs\ExecuteDTO;
use App\Models\ChartOfAccount;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ChartOfAccountActions
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
        ?string $scope,
        ?string $systemKey,
        ?string $accountType,
        ?string $normalBalance,
        ?bool $isGroup,
        ?bool $isActive,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $eagerLoads = $execute?->pagination
            ? self::PAGINATED_LIST_EAGER_LOADS
            : self::TREE_LIST_EAGER_LOADS;

        $query = ChartOfAccount::with($eagerLoads)
            ->select('chart_of_accounts.*')
            ->whereCompanyId('chart_of_accounts', $companyId);

        $query->where(function ($query) use (
            $search,
            $parentId,
            $hasParent,
            $hasChildren,
            $scope,
            $systemKey,
            $accountType,
            $normalBalance,
            $isGroup,
            $isActive,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $search,
                $parentId,
                $hasParent,
                $hasChildren,
                $scope,
                $systemKey,
                $accountType,
                $normalBalance,
                $isGroup,
                $isActive,
            ) {
                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('chart_of_accounts.code', 'like', '%'.$search.'%')
                            ->orWhere('chart_of_accounts.name', 'like', '%'.$search.'%')
                            ->orWhere('chart_of_accounts.system_key', 'like', '%'.$search.'%')
                            ->orWhere('chart_of_accounts.source_type', 'like', '%'.$search.'%')
                            ->orWhere('chart_of_accounts.remarks', 'like', '%'.$search.'%');
                    });
                }

                if ($parentId) {
                    $query->where('chart_of_accounts.parent_id', $parentId);
                }

                if (! is_null($hasParent)) {
                    if ($hasParent) {
                        $query->whereNotNull('chart_of_accounts.parent_id');
                    } else {
                        $query->whereNull('chart_of_accounts.parent_id');
                    }
                }

                if (! is_null($hasChildren)) {
                    $hasChildren
                        ? $query->whereHas('children')
                        : $query->whereDoesntHave('children');
                }

                if (! is_null($scope)) {
                    $query->where('chart_of_accounts.scope', $scope);
                }

                if (! is_null($systemKey)) {
                    $query->where('chart_of_accounts.system_key', $systemKey);
                }

                if (! is_null($accountType)) {
                    $query->where('chart_of_accounts.account_type', $accountType);
                }

                if (! is_null($normalBalance)) {
                    $query->where('chart_of_accounts.normal_balance', $normalBalance);
                }

                if (! is_null($isGroup)) {
                    $query->where('chart_of_accounts.is_group', $isGroup);
                }

                if (! is_null($isActive)) {
                    $query->where('chart_of_accounts.is_active', $isActive);
                }
            });

            if ($includeId) {
                $query->orWhere('chart_of_accounts.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(chart_of_accounts.id, '.$includeId.') desc');
        }
        $query->orderBy('chart_of_accounts.code', 'asc')
            ->orderBy('chart_of_accounts.id', 'asc');

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
                    $scope ?? '[null]',
                    $systemKey ?? '[null]',
                    $accountType ?? '[null]',
                    $normalBalance ?? '[null]',
                    is_null($isGroup) ? '[null]' : ($isGroup ? 'true' : 'false'),
                    is_null($isActive) ? '[null]' : ($isActive ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_chart_of_account_'.implode('-', $cacheParams);

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

    public function read(ChartOfAccount $chartOfAccount): ChartOfAccount
    {
        return $chartOfAccount->load(self::TREE_LIST_EAGER_LOADS);
    }

    public function hasChildren(ChartOfAccount $chartOfAccount): bool
    {
        return $chartOfAccount->children()->exists();
    }

    public function create(array $data): ChartOfAccount
    {
        $timer_start = microtime(true);

        try {
            $parent = $data['parent_id'] ? ChartOfAccount::query()->find($data['parent_id']) : null;

            $chartOfAccount = new ChartOfAccount();
            $chartOfAccount->company_id = $data['company_id'];
            $chartOfAccount->scope = $data['scope'];
            $chartOfAccount->system_key = $data['system_key'];
            $chartOfAccount->parent_id = $data['parent_id'];
            $chartOfAccount->source_type = $data['source_type'];
            $chartOfAccount->source_id = $data['source_id'];
            $chartOfAccount->code = $data['code'];
            $chartOfAccount->name = $data['name'];
            $chartOfAccount->account_type = $data['account_type'];
            $chartOfAccount->normal_balance = $data['normal_balance'];
            $chartOfAccount->level = $parent ? $parent->level + 1 : 1;
            $chartOfAccount->is_group = $data['is_group'];
            $chartOfAccount->is_active = $data['is_active'];
            $chartOfAccount->remarks = $data['remarks'];
            $chartOfAccount->save();

            $this->flushCache();

            return $chartOfAccount->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(ChartOfAccount $chartOfAccount, array $data): ChartOfAccount
    {
        $timer_start = microtime(true);

        try {
            $parent = $data['parent_id'] ? ChartOfAccount::query()->find($data['parent_id']) : null;

            $chartOfAccount->parent_id = $data['parent_id'];
            $chartOfAccount->code = $data['code'];
            $chartOfAccount->name = $data['name'];
            $chartOfAccount->account_type = $data['account_type'];
            $chartOfAccount->normal_balance = $data['normal_balance'];
            $chartOfAccount->level = $parent ? $parent->level + 1 : 1;
            $chartOfAccount->is_group = $data['is_group'];
            $chartOfAccount->is_active = $data['is_active'];
            $chartOfAccount->remarks = $data['remarks'];
            $chartOfAccount->save();

            $this->flushCache();

            return $chartOfAccount->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(ChartOfAccount $chartOfAccount): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $chartOfAccount->delete();

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

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = ChartOfAccount::where('company_id', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $query->where('chart_of_accounts.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $query = ChartOfAccount::where('company_id', $companyId)
            ->where('name', '=', $name);

        if ($exceptId) {
            $query->where('chart_of_accounts.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
