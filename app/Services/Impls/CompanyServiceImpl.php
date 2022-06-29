<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Actions\RandomGenerator;
use App\Services\CompanyService;

use App\Models\User;
use App\Models\Company;
use App\Traits\CacheHelper;

class CompanyServiceImpl implements CompanyService
{
    use CacheHelper;

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
            if (!$usr) return null;

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

            DB::commit();

            $this->flushCache();

            return $company;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $userId, 
        string $search = '', 
        bool $paginate = true, 
        int $page = 1, 
        int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection
    {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$userId.'-'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            $usr = User::find($userId);
            if (!$usr) return null;
    
            $compIds = $usr->companies()->pluck('company_id');
            
            if (empty($search)) {
                $companies = Company::whereIn('id', $compIds)->latest();
            } else {
                $companies = Company::whereIn('id', $compIds)->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $companies->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $companies->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function read(Company $company): Company
    {
        return $company->with('branches.warehouses')->first();
    }

    public function getAllActiveCompany(
        int $userId, 
        ?array $with = []
    )
    {
        $timer_start = microtime(true);

        try {
            $usr = User::find($userId);
            if (!$usr) return null;
    
            $compIds = $usr->companies()->pluck('company_id');

            $companies = Company::where('status', '=', 1)->whereIn('id',  $compIds);

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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(
        Company $company,
        array $companyArr
    ): Company
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $company->update([
                'code' => $companyArr['code'],
                'name' => $companyArr['name'],
                'address' => $companyArr['address'],
                'default' => $companyArr['default'],
                'status' => $companyArr['status']
            ]);

            DB::commit();

            $this->flushCache();

            return $company->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        return $code;
    }

    public function isUniqueCode(string $code, int $userId, ?int $exceptId = null): bool
    {
        $user = User::find($userId);

        if ($user->companies->count() == 0) return true;

        $result = $user->companies()->where('code', '=' , $code);

        if($exceptId)
            $result = $result->get()->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }

    public function isDefaultCompany(Company $company): bool
    {
        $result = $company->default;
        return is_null($result) ? false : $result;
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function getCompanyById(int $companyId): Company
    {
        return Company::find($companyId)->first();
    }

    public function getDefaultCompany(User $user): Company
    {
        return $user->companies()->where('default','=', true)->first();
    }
}
