<?php

namespace App\Services\Impls;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $name;
            $last_name = '';
        }

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'status' => 1,
        );

        $rolesId = array(Role::where('name', Config::get('const.DEFAULT.ROLE.USER'))->first()->id);

        $usr = $this->create(
            $name,
            $email,
            $password,
            $rolesId,
            $profile
        );

        return $usr;
    }

    public function create($name, $email, $password, $rolesId, $profile)
    {
        DB::beginTransaction();

        try {
            //throw New \Exception('Test Exception From Services');

            $usr = new User();
            $usr->name = $name;
            $usr->email = $email;
            $usr->password = Hash::make($password);

            $usr->password_changed_at = Carbon::now();

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

            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function flushCache($id)
    {
        Cache::forget('readAll'.$id);
        Cache::forget('readById'.$id);
    }

    public function read($parameters = null)
    {
        if ($parameters == null) return null;

        if (array_key_exists('readAll', $parameters)) {
            $perPage = array_key_exists('perPage', $parameters) ? $parameters['perPage'] : Config::get('const.DEFAULT.PAGINATION_LIMIT');

            if (array_key_exists('search', $parameters) && !empty($parameters['search'])) {
                return User::with('profile')
                            ->where('email', 'like', '%'.$parameters['search'].'%')
                            ->orWhere('name', 'like', '%'.$parameters['search'].'%')
                            ->orWhereHas('profile', function ($query) use($parameters) {
                                $query->where('first_name', 'like', '%'.$parameters['search'].'%')
                                    ->orWhere('last_name', 'like', '%'.$parameters['search'].'%');
                            })->paginate($perPage);
            }

            return User::with('roles', 'profile', 'settings')->paginate($perPage);
        }

        if (array_key_exists('readById', $parameters))  {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED'))
                return User::with('roles', 'profile')->find($parameters['readById']);

            return Cache::remember('readById'.$parameters['readById'], Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.1_HOUR'), function() use ($parameters) {
                return User::with('roles', 'profile')->find($parameters['readById']);
            });
        }

        if (array_key_exists('readByEmail', $parameters)) {
            return User::where('email', '=', $parameters['readByEmail'])->first();
        }

        if (array_key_exists('allExceptMe', $parameters)) {
            return User::where('email', '!=', $parameters['allExceptMe'])->get();
        }

        return null;
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
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
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
                'key' => 'PREFS.THEME',
                'value' => 'side-menu-light-full',
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
}
