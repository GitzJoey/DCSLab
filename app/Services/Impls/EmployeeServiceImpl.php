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
        $company_id, 
        $name, 
        $email,
        $rolesId, 
        $profile
    )
    {
        DB::beginTransaction();

        try {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->save();
            $user_id = $user->id;

            $pa = new Profile();
            $pa->first_name = array_key_exists('first_name', $profile) ? $profile['first_name']:null;
            $pa->last_name = array_key_exists('last_name', $profile) ? $profile['last_name']:null;
            $pa->address = array_key_exists('address', $profile) ? $profile['address']:null;
            $pa->city = array_key_exists('city', $profile) ? $profile['city']:null;
            $pa->postal_code = array_key_exists('postal_code', $profile) ? $profile['postal_code']:null;
            $pa->country = array_key_exists('country', $profile) ? $profile['country']:null;
            $pa->tax_id = array_key_exists('tax_id', $profile) ? $profile['tax_id']:null;
            $pa->ic_num = array_key_exists('ic_num', $profile) ? $profile['ic_num']:null;
            $pa->status = array_key_exists('status', $profile) ? $profile['status']:null;
            $pa->img_path = array_key_exists('img_path', $profile) ? $profile['img_path']:null;
            $pa->remarks = array_key_exists('remarks', $profile) ? $profile['remarks']:null;
            $user->profile()->save($pa);

            $settings = $this->createDefaultSetting();
            $user->settings()->saveMany($settings);
            
            $user->attachRoles($rolesId);

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
        $name, 
        $email, 
        $rolesId, 
        $profile
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
            // $user->password = $password;
            $retval = $user->save();
            
            if ($profile != null) {
                $pa = $user->profile()->first();

                $retval += $pa->update([
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'address' => $profile['address'],
                    'city' => $profile['city'],
                    'postal_code' => $profile['postal_code'],
                    'country' => $profile['country'],
                    'status' => $profile['status'],
                    'tax_id' => $profile['tax_id'],
                    'ic_num' => $profile['ic_num'],
                    'img_path' => array_key_exists('img_path', $profile ) ? $profile['img_path']:$pa->img_path,
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