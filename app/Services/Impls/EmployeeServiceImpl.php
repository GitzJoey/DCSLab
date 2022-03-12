<?php

namespace App\Services\Impls;

use Exception;
use App\Models\User;

use App\Models\Employee;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        int $company_id,
        array $user,
        string $join_date,
        int $status
    ): ?Employee
    {
        DB::beginTransaction();

        try {
            $userService = app(UserService::class);
            $user_id = $userService->create(
                $user[0]['name'],
                $user[0]['email'],
                $user[0]['password'],
                $user[0]['rolesId'],
                $user[0]['profile']
            );
            $user_id = $user_id->id;
            
            $employee = new Employee();
            $employee->company_id = $company_id;
            $employee->user_id = $user_id;
            $employee->join_date = $join_date;
            $employee->status = $status;

            $employee->save();

            DB::commit();

            return $employee;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $perPage = 10
    )
    {
        $employee = Employee::with('company', 'user.profile')->whereCompanyId($companyId);

        if (empty($search)) {
            $employee = $employee->latest();
        } else {
            $employee = $employee->where('name', 'like', '%'.$search.'%')->latest();
        }

        if ($paginate) {
            $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
            return $employee->paginate($perPage);
        } else {
            return $employee->get();
        }
    }

    public function update(
        int $id,
        int $company_id,
        int $user_id,
        string $join_date,
        int $status
    ): ?Employee
    {
        DB::beginTransaction();

        try {
            $employee = Employee::find($id);
    
            $employee->update([
                'company_id' => $company_id,
                'user_id' => $user_id,
                'join_date' => $join_date,
                'status' => $status,
            ]);

            $user_id = new User;

            DB::commit();

            return $employee->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $userId, int $id): bool
    {
        DB::beginTransaction();

        $retval = false;
        try {
            $employee = Employee::find($id);
            $retval = $employee->delete();

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
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