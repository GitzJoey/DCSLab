<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\EmployeeService;
use App\Models\Employee;

class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        $company_id,
        $name,
        $email,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $employee = new Employee();
            $employee->company_id = $company_id;
            $employee->name = $name;
            $employee->email = $email;
            $employee->address = $address;
            $employee->city = $city;
            $employee->contact = $contact;
            $employee->remarks = $remarks;
            $employee->status = $status;

            $employee->save();

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
        $company_id,
        $name,
        $email,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $employee = Employee::where('id', '=', $id);
    
            $retval = $employee->update([
                'company_id' => $company_id,
                'name' => $name,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'remarks' => $remarks,
                'status' => $status
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
        $employee = Employee::find($id);

        return $employee->delete();
    }
}