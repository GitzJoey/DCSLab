<?php

namespace App\Actions\Branch;

use App\Actions\Company\CompanyActions;
use App\DTOs\ExecuteDTO;
use App\Models\Branch;
use App\Models\Company;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class BranchActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Branch
    {
        $timer_start = microtime(true);

        try {
            $branch = new Branch();
            $branch->company_id = $data['company_id'];
            $branch->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $branch->name = $data['name'];
            $branch->address = $data['address'];
            $branch->city = $data['city'];
            $branch->contact = $data['contact'];
            $branch->is_main = $data['is_main'];
            $branch->remarks = $data['remarks'];
            $branch->status = $data['status'];

            $branch->save();

            $this->flushCache();

            return $branch;
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
        ?bool $isMain,
        ?int $status,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Branch::with('company')->select('branches.*')
            ->whereCompanyId($companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $isMain, $status, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search, $isMain, $status) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                if ($isMain !== null) {
                    $query->where('branches.is_main', '=', $isMain);
                }

                if ($status !== null) {
                    $query->where('branches.status', '=', $status);
                }
            });

            if ($includeId) {
                $query->orWhere('branches.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(branches.id, '.$includeId.') desc');
        }
        $query->orderBy('branches.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    is_null($isMain) ? '[null]' : ($isMain ? 'true' : 'false'),
                    $status ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_'.implode('-', $cacheParams);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) return $cacheData;
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

    public function read(Branch $branch): Branch
    {
        return $branch->load('company');
    }

    public function getById(int $branchId): Branch
    {
        return Branch::find($branchId);
    }

    public function isMain(Branch $branch): bool
    {
        $result = $branch->is_main;

        return is_null($result) ? false : $result;
    }

    public function update(
        Branch $branch,
        array $data,
    ): Branch {
        $timer_start = microtime(true);

        try {
            $branch->code = $this->generateUniqueCode($branch->company_id, $data['code'], $branch->id);
            $branch->name = $data['name'];
            $branch->address = $data['address'];
            $branch->city = $data['city'];
            $branch->contact = $data['contact'];
            $branch->is_main = $data['is_main'];
            $branch->remarks = $data['remarks'];
            $branch->status = $data['status'];

            $branch->save();

            $this->flushCache();

            return $branch->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function resetMainByCompany(int $companyId): bool
    {
        $timer_start = microtime(true);

        try {
            $company = (new CompanyActions())->getById($companyId);

            return $company->branches()->update(['is_main' => false]);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Branch $branch): bool
    {
        $timer_start = microtime(true);

        $retval = false;
        try {
            $retval = $branch->delete();

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
            $count = $company->branches()->withTrashed()->count() + 1 + $tryCount;
            $code = 'BC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->branches()->count() == 0) return true;

        $query = $company->branches()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('branches.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->branches()->count() == 0) return true;

        $query = $company->branches()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('branches.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
