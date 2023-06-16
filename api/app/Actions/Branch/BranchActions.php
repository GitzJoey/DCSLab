<?php

namespace App\Actions\Branch;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Actions\Randomizer\RandomizerActions;
use App\Models\Branch;
use App\Models\Company;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BranchActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(
        array $branchArr
    ): Branch {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $company_id = $branchArr['company_id'];
            $code = $branchArr['code'];
            $name = $branchArr['name'];
            $address = $branchArr['address'];
            $city = $branchArr['city'];
            $contact = $branchArr['contact'];
            $is_main = $branchArr['is_main'];
            $remarks = $branchArr['remarks'];
            $status = $branchArr['status'];

            $company = Company::find($company_id);
            if ($company->branches()->count() == 0) {
                $is_main = true;
                $status = 1;
            }

            $branch = new Branch();
            $branch->company_id = $company_id;
            $branch->code = $code;
            $branch->name = $name;
            $branch->address = $address;
            $branch->city = $city;
            $branch->contact = $contact;
            $branch->is_main = $is_main;
            $branch->remarks = $remarks;
            $branch->status = $status;
            $branch->save();

            $coaActions = app(ChartOfAccountActions::class);
            if ($branch->company->chartOfAccounts()->count() == 0) {
                $coaActions->createDefaultAccountPerCompany($branch->company_id);
            }

            $coaActions->createDefaultAccountPerBranch(
                $branch->company_id,
                $branch->id
            );

            DB::commit();

            $this->flushCache();

            return $branch;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (! $companyId) {
                return null;
            }

            $branch = count($with) != 0 ? Branch::with($with) : Branch::with('company');
            $branch = $branch->whereCompanyId($companyId);

            if ($withTrashed) {
                $branch = $branch->withTrashed();
            }

            if (empty($search)) {
                $branch = $branch->latest();
            } else {
                $branch = $branch->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $branch->paginate(
                    perPage: $perPage,
                    page: $page
                );
            } else {
                $result = $branch->get();
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

    public function read(Branch $branch): Branch
    {
        return $branch->with('warehouses')->first();
    }

    public function getBranchByCompany(int $companyId = 0, Company $company = null): Collection
    {
        if (! is_null($company)) {
            return $company->branches;
        }

        if ($companyId != 0) {
            return Branch::where('company_id', '=', $companyId)->where('status', '=', 1)->get();
        }

        return null;
    }

    public function getMainBranchByCompany(int $companyId = 0, Company $company = null): Branch
    {
        if (! is_null($company)) {
            return $company->branches()->where('is_main', '=', true)->first();
        }

        if ($companyId != 0) {
            return Branch::where('company_id', '=', $companyId)->where('is_main', '=', true)->first();
        }

        return null;
    }

    public function update(
        Branch $branch,
        array $branchArr,
    ): Branch {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $code = $branchArr['code'];
            $name = $branchArr['name'];
            $address = $branchArr['address'];
            $city = $branchArr['city'];
            $contact = $branchArr['contact'];
            $is_main = $branchArr['is_main'];
            $remarks = $branchArr['remarks'];
            $status = $branchArr['status'];

            $branch->update([
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'is_main' => $is_main,
                'remarks' => $remarks,
                'status' => $status,
            ]);

            DB::commit();

            $this->flushCache();

            return $branch->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function resetMainBranch(int $companyId = 0, Company $company = null): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($companyId != 0) {
                $retval = Branch::where('company_id', '=', $companyId)->update(['is_main' => false]);
            } elseif (! is_null($company)) {
                $retval = $company->branches()->update(['is_main' => false]);
            } else {
                $retval = 0;
            }

            DB::commit();

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

    public function delete(Branch $branch): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $retval = $branch->delete();

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
        $result = Branch::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
