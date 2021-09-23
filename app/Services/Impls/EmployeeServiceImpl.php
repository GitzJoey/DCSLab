<?php

namespace App\Services\Impls;

use App\Models\Employee;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\EmployeeService;
use App\Models\User;

class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        $name,
        $email
    )
    {
        DB::beginTransaction();

        try {
            $employee = new Employee();
            $employee->name = $name;
            $employee->email = $email;

            $employee->save();

            // if (env('AUTO_VERIFY_EMAIL', true))
            //     $employee->markEmailAsVerified();

            DB::commit();

            return $employee->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function readAll()
    {
        return Employee::paginate();
    }

    public function read($userId)
    {
        return Employee::where('id', '=', $userId)->paginate();
    }

    public function getEmployeeByEmail($email)
    {
        return Employee::where('email', '=', $email)->first();
    }

    public function update(
        $id,
        $name,
        $email
    )
    {
        DB::beginTransaction();

        try {
            $employee = User::where('id', '=', $id);
    
            $retval = $employee->update([
                'name' => $name,
                'email' => $email,
            ]);
    
            DB::commit();
    
            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function getEmployeeById($id)
    {
        return Employee::find($id);    
    }

    public function delete($id)
    {
        $employee = User::find($id);

        return $employee->delete();
    }
}