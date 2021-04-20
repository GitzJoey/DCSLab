<?php

namespace App\Services\Impls;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Models\Setting;

use App\Services\UserService;

class UserServiceImpl implements UserService
{

    public function create($name, $email, $password, $rolesId, $profile, $setting)
    {
        DB::beginTransaction();

        try {
            $usr = new User();
            $usr->name = $name;
            $usr->email = $email;
            $usr->password = Hash::make($password);

            $usr->created_at = Carbon::now();
            $usr->updated_at = Carbon::now();

            $usr->save();

            $pa = new Profile();
            $pa->first_name = $profile[0]['first_name'];
            $pa->last_name = $profile[0]['last_name'];
            $pa->address = $profile[0]['address'];
            $pa->ic_num = $profile[0]['ic_num'];

            $usr->profile()->save($pa);

            $usr->attachRole(Role::where('name', $rolesId)->first());

            return $usr->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return User::with('profile', 'settings', 'roles')->paginate(Config::get('const.PAGINATION_LIMIT'));
    }

    public function readCreatedById($id)
    {
        return User::with('profile', 'settings', 'roles')->where('created_by', $id)->paginate(Config::get('const.PAGINATION_LIMIT'));
    }

    public function getMyProfile($id)
    {
        return User::with('profile', 'settings', 'roles')->where('id', $id);
    }

    public function update($id, $name, $email, $password, $rolesId, $profile, $settings)
    {
        DB::beginTransaction();

        try {

            $retval = '';

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function ban($id, $reason)
    {

    }

    public function createDefaultSetting()
    {

    }
}
