<?php

namespace App\Services\Impls;

use Exception;

use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Traits\CacheHelper;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Pagination\Paginator;

class EmployeeServiceImpl implements EmployeeService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $email,
        string $address,
        string $city,
        string $postal_code,
        string $country,
        string $tax_id,
        string $ic_num,
        string $img_path = '',
        string $join_date,
        string $remarks,
        int $status
    ): ?Employee
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $first_name = '';
            $last_name = '';
            if ($name == trim($name) && strpos($name, ' ') !== false) {
                $pieces = explode(" ", $name);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $name;
            }

            $rolesId = [];
            array_push($rolesId, Role::where('name', '=', 'user')->first()->id);

            $profile = array (
                'first_name' => $first_name,
                'last_name' => $last_name,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postal_code,
                'country' => $country,
                'tax_id' => $tax_id,
                'ic_num' => $ic_num,
                'img_path' => $img_path,
                'remarks' => $remarks,
                'status' => $status
            );

            $userService = app(UserService::class);
            $user = $userService->create(
                name: $name,
                email: $email,
                password: (new RandomGenerator())->generateNumber(10000000, 999999999),
                rolesId: $rolesId,
                profile: $profile
            );
            $user_id = $user->id;
            
            $employee = new Employee();
            $employee->company_id = $company_id;
            $employee->user_id = $user_id;
            $employee->code = $code;
            $employee->join_date = $join_date;
            $employee->status = $status;

            $employee->save();

            DB::commit();

            $this->flushCache();

            return $employee;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null
    {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            if (!$companyId) return null;

            $employee = Employee::with('company', 'user.profile')
                        ->whereCompanyId($companyId);
    
            if (empty($search)) {
                $employee = $employee->latest();
            } else {
                $employee = $employee->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                $result = $employee->paginate($perPage);
            } else {
                $result = $employee->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(
        int $id,
        string $code,
        string $name,
        string $email,
        string $address,
        string $city,
        string $postal_code,
        string $country,
        string $tax_id,
        string $ic_num,
        string $img_path = '',
        ?string $join_date = null,
        string $remarks,
        int $status
    ): ?Employee
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $employee = Employee::find($id);
            $employee->code = $code;
            $employee->join_date = is_null($join_date) ? $employee->join_date : $join_date;
            $employee->status = $status;
            $employee->save();

            $rolesId = [];
            array_push($rolesId, Role::where('name', '=', 'user')->first()->id);

            $first_name = '';
            $last_name = '';
            if ($name == trim($name) && strpos($name, ' ') !== false) {
                $pieces = explode(" ", $name);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $name;
            }
    
            $profile = array (
                'first_name' => $first_name,
                'last_name' => $last_name,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postal_code,
                'country' => $country,
                'tax_id' => $tax_id,
                'ic_num' => $ic_num,
                'img_path' => $img_path,
                'remarks' => $remarks,
                'status' => $status,
            );

            $userService = app(UserService::class);
            $userService->update(
                id: $employee->user_id,
                name: $name,
                rolesId: $rolesId,
                profile: $profile,
                settings: null
            );

            DB::commit();

            $this->flushCache();

            return $employee->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);            
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $employee = Employee::find($id);

            if ($employee) {
                $user = User::find($employee->user_id);
                $user->profile->status = 0;

                $retval = $employee->delete();            
            }

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Employee::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}