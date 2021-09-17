<?php

namespace App\Services\Impls;

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
            $employee = new User();
            $employee->name = $name;
            $employee->email = $email;

            $employee->save();

            DB::commit();

            return $employee->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return User::paginate();
    }

    public function getEmployeeByEmail($email)
    {
        return User::where('email', '=', $email)->first();
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
        return User::find($id);
    }

    public function delete($id)
    {
        $employee = User::find($id);

        return $employee->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = User::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = User::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}