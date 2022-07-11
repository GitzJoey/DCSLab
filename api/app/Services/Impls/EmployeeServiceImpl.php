<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Role;
use App\Models\User;
use App\Services\EmployeeService;
use App\Services\UserService;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    ): Employee {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $rolesArr = [];
            array_push($rolesArr, Role::where('name', '=', UserRoles::POS_EMPLOYEE->value)->first()->id);

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
            foreach ($accessesArr as $access) {
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $companyId,
        string $search,
        bool $paginate,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (!$companyId) {
                return null;
            }

            $relationship = ['company', 'user.profile', 'employeeAccesses.branch'];

            $employee = Employee::with('company', 'employeeAccesses.branch')
            ->whereCompanyId($companyId);

            if (empty($search)) {
                $employee = $employee->latest();
            } else {
                $employee = Employee::with($relationship)
                                ->whereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', '%'.$search.'%');
                                })
                                ->whereCompanyId($companyId)
                                ->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $employee->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $employee->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(Employee $employee): Employee
    {
        return $employee->with('company', 'user.profile', 'employeeAccesses')->first();
    }

    public function update(
        Employee $employee,
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $userService = app(UserService::class);
            $userService->update(
                user: $employee->user,
                userArr: $userArr,
                profileArr: $profileArr
            );

            $employee->update([
                'code' => $employeeArr['code'],
                'join_date' => $employeeArr['join_date'],
                'status' => $employeeArr['status'],
            ]);

            $employee->employeeAccesses()->delete();

            if (!empty($accessesArr)) {
                $newAccesses = [];

                foreach ($accessesArr as $access) {
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Employee::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
