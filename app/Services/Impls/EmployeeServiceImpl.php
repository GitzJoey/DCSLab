<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\EmployeeService;
use App\Models\Employee;
use App\Models\User;
use App\Models\Setting;

class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        $company_id,
        $user_id
    )
    {
        DB::beginTransaction();

        try {
            $employee = new Employee();
            $employee->company_id = $company_id;
            $employee->user_id = $user_id;
            $employee->save();

            DB::commit();

            return $employee->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read($userId)
    {
        $user = User::find($userId);
        $company_list = $user->companies()->pluck('company_id');
        return Employee::with('company', 'user.profile')->whereIn('company_id', $company_list)->paginate();
    }

    public function getEmployeeByEmail($email)
    {
        return Employee::where('email', '=', $email)->first();
    }

    public function update(
        $id,
        $company_id, 
        $user_id
    )
    {
        DB::beginTransaction();

        try {
            $employee = Employee::find($id);
            $employee->company_id = $company_id;
            $retval = $employee->save();

            DB::commit();
    
            return $retval;
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

    public function createDefaultSetting()
    {
        $list = array (
            new Setting(array(
                'type' => 'KEY_VALUE',
                'key' => 'THEME.CODEBASE',
                'value' => 'corporate',
            )),
            new Setting(array(
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.DATE_FORMAT',
                'value' => 'yyyy_MM_dd',
            )),
            new Setting(array(
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.TIME_FORMAT',
                'value' => 'hh_mm_ss',
            )),
        );

        return $list;
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