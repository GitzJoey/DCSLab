<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\EmployeeService;
use App\Models\Employee;
use App\Models\User;
use App\Models\Profile;
use App\Models\Setting;

class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        $company_id, $name, $email, $password, 
        $address, $city, $postal_code, $country, $tax_id, $ic_num, $img_path, $status, $remarks,
        $role_id
    )
    {
        DB::beginTransaction();

        try {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->save();
            $user_id = $user->id;

            $profile = new Profile();
            $profile->img_path = $img_path;
            $profile->address = $address;
            $profile->city = $city;
            $profile->postal_code = $postal_code;
            $profile->country = $country;
            $profile->tax_id = $tax_id;
            $profile->ic_num = $ic_num;
            $profile->status = $status;
            $profile->remarks = $remarks;            

            $user->profile()->save($profile);

            $settings = $this->createDefaultSetting();
            $user->settings()->saveMany($settings);
            
            $user->attachRoles($role_id);

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
        // return Employee::with('company')->where('id', '=', $userId)->paginate();
        return Employee::with('company', 'user.profile')->paginate();
    }

    public function getEmployeeByEmail($email)
    {
        return Employee::where('email', '=', $email)->first();
    }

    public function update(
        $id,
        $company_id, 
        $name, $email, $password, 
        $address, $city, $postal_code, $country, $tax_id, $ic_num, $img_path, $status, $remarks
    )
    {
        DB::beginTransaction();

        try {
            $employee = Employee::find($id);
            $employee->company_id = $company_id;
            $retval = $employee->save();

            $user_id = User::find($employee->user_id);
            $user_id = $user_id->id;
            $user = User::find($user_id);
            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $retval = $user->save();
            
            $profile_id = Profile::where('user_id', $user_id)->first();
            $profile_id = $profile_id->id;
            $profile = Profile::find($profile_id);
            $profile->address = $address;
            $profile->city = $city;
            $profile->postal_code = $postal_code;
            $profile->country = $country;
            $profile->tax_id = $tax_id;
            $profile->ic_num = $ic_num;
            $profile->img_path = array_key_exists('img_path', $profile) ? $profile['img_path']:null;
            $profile->status = $status;
            $profile->remarks = $remarks;
            $retval = $profile->save();

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
                    'address' => $profile['address'],
                    'city' => $profile['city'],
                    'postal_code' => $profile['postal_code'],
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