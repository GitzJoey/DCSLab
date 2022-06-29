<?php

namespace App\Services\Impls;

use Exception;

use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Traits\CacheHelper;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Models\EmployeeAccess;
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
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $rolesArr = [];
            array_push($rolesId, Role::where('name', '=', UserRoles::POS_EMPLOYEE->value)->first()->id);

            $userService = app(UserService::class);
            
            $user = $userService->create(
                $userArr,
                $rolesArr,
                $profileArr
            );
            
            $employee = new Employee();
            $employee->company_id = $employeeArr['company_id'];
            $employee->user_id = $user->id;
            $employee->code = $employeeArr['code'];
            $employee->join_date = $employeeArr['join_date'];
            $employee->status = $employeeArr['status'];

            $employee->save();

            $newAccesses = [];
            foreach($accessesArr as $access) {
                $newAccess = new EmployeeAccess();
                $newAccess->employee_id = $employee->id;
                $newAccess->branch_id = $access['branch_id'];

                array_push($newAccesses, $newAccess);
            }
            $employee->employeeAccesses()->saveMany($newAccesses);

            DB::commit();

            $this->flushCache();

            return $employee;
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
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection
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

            $employee = Employee::with('company', 'user.profile', 'employeeAccesses.branch')
                        ->whereCompanyId($companyId);
    
            if (empty($search)) {
                $employee = $employee->latest();
            } else {
                $employee = $employee->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $employee->paginate($perPage);
            } else {
                $result = $employee->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(Employee $employee): Employee
    {
        return $employee->with('company, user.profile, employeeAccesses')->first();
    }

    public function update(
        Employee $employee,
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $userService = app(UserService::class);
            $userService->update(
                user: $employee->user,
                userArr: $userArr,
                profile: $profileArr
            );

            $employee->update([
                'code' => $employeeArr['code'],
                'join_date' => $employeeArr['join_date'],
                'status' => $employeeArr['status']
            ]);

            $employee->employeeAccesses()->delete();
            
            if (!empty($accessesArr)) {
                $newAccesses = [];

                foreach($accessesArr as $access) {
                    $newAccess = new EmployeeAccess();
                    $newAccess->employee_id = $employee->id;
                    $newAccess->branch_id = $access['branch_id'];
    
                    array_push($newAccesses, $newAccess);
                }

                $employee->employeeAccesses()->saveMany($newAccesses);
            }

            DB::commit();

            $this->flushCache();

            return $employee->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;            
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(Employee $employee): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $employee->employeeAccesses()->delete();

            $user = User::find($employee->user_id);
            $user->profile->status = RecordStatus::INACTIVE->value;

            $retval = $employee->delete();            

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