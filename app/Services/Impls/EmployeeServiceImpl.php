<?php

namespace App\Services\Impls;

use Exception;
use App\Models\User;

use App\Models\Employee;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        int $company_id,
        int $user_id,
    ): ?Employee
    {
        DB::beginTransaction();

        try {

            $employee = new Employee();
            $employee->company_id = $company_id;
            $employee->user_id = $user_id;

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
        // int $userId,
        string $search = '',
        bool $paginate = true,
        int $perPage = 10
    )
    {
        // $usr = User::find($userId);
        // if (!$usr) return null;
        
        if (!$companyId) return null;

        $employee = Employee::with('company')
                    ->whereCompanyId($companyId);

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
    ): ?Employee
    {
        DB::beginTransaction();

        try {
            $employee = Employee::find($id);
    
            $employee->update([
                'company_id' => $company_id,
                'user_id' => $user_id,
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

    public function updateProfile($id, $profile)
    {
        DB::beginTransaction();

        try {
            $retval = 0;

            $employee = Employee::find($id);

            if ($profile != null) {
                $profile = $employee->profile()->first();

                $retval += $profile->update([
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'address' => $profile['address'],
                    'city' => $profile['city'],
                    'postal_code' => $profile['postal_code'],
                    'country' => $profile['country'],
                    'status' => $profile['status'],
                    'tax_id' => $profile['tax_id'],
                    'ic_num' => $profile['ic_num'],
                    'img_path' => array_key_exists('img_path', $profile ) ? $profile['img_path']:$profile->img_path,
                    'remarks' => $profile['remarks']
                ]);
            }

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();

        $retval = false;
        try {
            $employee = Employee::find($id);

            if ($employee) {
                $retval = $employee->delete();
            }

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