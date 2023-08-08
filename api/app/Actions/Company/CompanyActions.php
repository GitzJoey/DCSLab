<?php

namespace App\Actions\Company;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Actions\Randomizer\RandomizerActions;
use App\Models\Company;
use App\Models\User;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CompanyActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $companyArr): Company
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $code = $companyArr['code'];
            $name = $companyArr['name'];
            $address = $companyArr['address'];
            $default = $companyArr['default'];
            $status = $companyArr['status'];
            $userId = $companyArr['user_id'];

            $usr = User::find($userId);
            if (! $usr) {
                return null;
            }

            if ($usr->companies()->count() == 0) {
                $default = true;
                $status = 1;
            }

            $company = new Company();
            $company->code = $code;
            $company->name = $name;
            $company->address = $address;
            $company->default = $default;
            $company->status = $status;
            $company->save();

            $usr->companies()->attach([$company->id]);

            $coaAction = app(ChartOfAccountActions::class);
            $coaAction->createDefaultAccountPerCompany($company->id);

            DB::commit();

            $this->flushCache();

            return $company;
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
        int $userId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheKey = 'readAny_'.$userId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $relationship = ['branches'];
            $relationship = count($with) > 0 ? $with : $relationship;
            $query = Company::with($relationship);
           
            $user = User::find($userId);
            if (! $user) return null;
            $companyIds = $user->companies()->pluck('company_id');            
            $query = $query->whereIn('id', $companyIds);
           
            if (!empty($search)) {
                $query = $query->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($withTrashed) $query = $query->withTrashed();

            $query = $query->latest();

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $query->paginate(perPage: $perPage, page: $page);
            } else {
                $result = $query->get();
            }

            $recordsCount = $result->count();

            $this->saveToCache($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
        }
    }

    public function read(Company $company): Company
    {
        return $company->with('branches.warehouses')->first();
    }

    public function isDefaultCompany(Company $company): bool
    {
        $result = $company->default;

        return is_null($result) ? false : $result;
    }

    public function getCompanyById(int $companyId): Company
    {
        return Company::find($companyId)->first();
    }

    public function getDefaultCompany(User $user): Company
    {
        return $user->companies()->where('default', '=', true)->first();
    }

    public function getAllActiveCompany(
        int $userId,
        ?array $with = []
    ) {
        $timer_start = microtime(true);

        try {
            $usr = User::find($userId);
            if (! $usr) {
                return null;
            }

            $compIds = $usr->companies()->pluck('company_id');

            $companies = Company::where('status', '=', 1)->whereIn('id', $compIds);

            if (in_array('branches', $with)) {
                $companies = $companies->with(['branches' => function ($query) {
                    $query->where('status', '=', 1);
                }]);
            }

            if (in_array('warehouses', $with)) {
                $companies = $companies->with(['warehouses' => function ($query) {
                    $query->where('status', '=', 1);
                }]);
            }

            if (in_array('employees', $with)) {
                $companies = $companies->with(['employees' => function ($query) {
                    $query->where('status', '=', 1);
                }]);
            }

            return $companies->get();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        Company $company,
        array $companyArr
    ): Company {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $company->update([
                'code' => $companyArr['code'],
                'name' => $companyArr['name'],
                'address' => $companyArr['address'],
                'default' => $companyArr['default'],
                'status' => $companyArr['status'],
            ]);

            DB::commit();

            $this->flushCache();

            return $company->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function resetDefaultCompany(User $user): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $compIds = $user->companies()->pluck('company_id');

            $retval = Company::whereIn('id', $compIds)
                ->update(['default' => 0]);

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

    public function delete(Company $company): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $company->delete();

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

    public function isUniqueCode(string $code, int $userId, ?int $exceptId = null): bool
    {
        $user = User::find($userId);

        if ($user->companies->count() == 0) {
            return true;
        }

        $result = $user->companies()->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->get()->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
