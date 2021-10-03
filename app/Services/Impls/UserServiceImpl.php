<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

use Illuminate\Mail\Message;

use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Models\Setting;

use App\Services\UserService;

class UserServiceImpl implements UserService
{
    public function register($name, $email, $password, $terms)
    {
        $usr = new User();
        $usr->name = $name;
        $usr->email = $email;
        $usr->password = Hash::make($password);

        $usr->save();

        $profile = new Profile();
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $profile->first_name = $pieces[0];
            $profile->last_name = $pieces[1];
        } else {
            $profile->first_name = $name;
        }

        $profile->status = 1;

        $usr->profile()->save($profile);

        $settings = $this->createDefaultSetting();

        $usr->settings()->saveMany($settings);

        $user_role = Role::where('name', Config::get('const.DEFAULT.ROLE.USER'))->first();

        $usr->attachRole($user_role);

        if (env('AUTO_VERIFY_EMAIL', true))
            $usr->markEmailAsVerified();

        return $usr;
    }

    public function create($name, $email, $password, $rolesId, $profile)
    {
        DB::beginTransaction();

        try {
            $usr = new User();
            $usr->name = $name;
            $usr->email = $email;
            $usr->password = Hash::make($password);

            $usr->save();

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

            $usr->profile()->save($pa);

            $settings = $this->createDefaultSetting();
            $usr->settings()->saveMany($settings);

            $usr->attachRoles($rolesId);

            if (env('AUTO_VERIFY_EMAIL', true))
                $usr->markEmailAsVerified();

            DB::commit();

            return $usr->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return User::with('roles', 'profile', 'settings')->paginate(Config::get('const.PAGINATION_LIMIT'));
    }

    public function readCreatedById($id)
    {
        return User::with('profile', 'settings', 'roles')->where('created_by', $id)->paginate(Config::get('const.PAGINATION_LIMIT'));
    }

    public function getMyProfile($id)
    {
        return User::with('profile', 'settings', 'roles')->where('id', $id);
    }

    public function update($id, $name, $rolesId, $profile, $settings)
    {
        DB::beginTransaction();

        try {
            $retval = 0;

            $usr = User::find($id);
            $retval += $usr->update([
                'name' => $name,
            ]);

            if ($profile != null) {
                $pa = $usr->profile()->first();

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

            $usr->syncRoles($rolesId);

            foreach ($settings as $key => $value) {
                $setting = $usr->settings()->where('key', $key)->first();
                if (!$setting || $value == null) continue;
                if ($setting->value != $value) {
                    $retval += $setting->update([
                        'value' => $value,
                    ]);
                }
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

            $usr = User::find($id);

            if ($profile != null) {
                $pa = $usr->profile()->first();

                $retval += $pa->update([
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'address' => $profile['address'],
                    'city' => $profile['city'],
                    'postal_code' => $profile['postal_code'],
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


    public function resetPassword($email)
    {
        $response = Password::sendResetLink(['email' => $email], function (Message $message) {
            $message->subject('Reset Password');
        });
    }

    public function resetTokens($id)
    {
        $usr = User::find($id);

        $usr->tokens()->delete();
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

    public function getUserById($id)
    {
        return User::with('roles', 'profile')->find($id);
    }

    public function getUserByEmail($email)
    {
        return User::where('email', '=', $email)->first();
    }

    public function getAllUserExceptMe($email)
    {
        return User::where('email', '!=', $email)->get();
    }

    public function setRoleForUserId($userId, $roleId)
    {
        DB::beginTransaction();

        try {
            $usr = User::find($userId);

            $usr->syncRoles($roleId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }
}
