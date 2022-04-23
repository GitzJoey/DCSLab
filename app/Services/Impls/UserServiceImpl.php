<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
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
    public function __construct()
    {
        
    }
    
    public function register(string $name, string $email, string $password, string $terms): ?User
    {
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];

            $name = str_replace(' ', '', $name);
        } else {
            $first_name = $name;
            $last_name = '';
        }

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'status' => ActiveStatus::ACTIVE,
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

    public function create(string $name, string $email, string $password, array $rolesId, array $profile): ?User
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            //throw New \Exception('Test Exception From Services');

            $usr = new User();
            $usr->name = $name;
            $usr->email = $email;

            if (empty($password)) {
                $usr->password = (new RandomGenerator())->generateAlphaNumeric(5);
                $usr->password_changed_at = null;
            } else {
                $usr->password = Hash::make($password);
                $usr->password_changed_at = Carbon::now();
            }

            $usr->save();

            $pa = new Profile();

            $pa->first_name = array_key_exists('first_name', $profile) ? $profile['first_name']:'';
            $pa->last_name = array_key_exists('last_name', $profile) ? $profile['last_name']:'';
            $pa->address = array_key_exists('address', $profile) ? $profile['address']:null;
            $pa->city = array_key_exists('city', $profile) ? $profile['city']:null;
            $pa->postal_code = array_key_exists('postal_code', $profile) ? $profile['postal_code']:null;
            $pa->country = array_key_exists('country', $profile) ? $profile['country']:null;
            $pa->tax_id = array_key_exists('tax_id', $profile) ? $profile['tax_id']:'';
            $pa->ic_num = array_key_exists('ic_num', $profile) ? $profile['ic_num']:'';
            $pa->status = array_key_exists('status', $profile) ? $profile['status']:1;
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
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function flushCache(int $id): void
    {
        Cache::tags([$id])->flush();
    }

    public function read(string $search = '', bool $paginate = true, int $perPage = 10)
    {
        $timer_start = microtime(true);
        try {
            $relationship = ['roles', 'profile', 'settings'];

            if (empty($search)) {
                $usr = User::with($relationship)->latest();
            } else {
                $usr = User::with($relationship)
                        ->where('email', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhereHas('profile', function ($query) use($search) {
                            $query->where('first_name', 'like', '%'.$search.'%')
                                    ->orWhere('last_name', 'like', '%'.$search.'%');
                        })->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $usr->paginate(abs($perPage));
            } else {
                return $usr->get();
            }    
        } catch (Exception $e) {
            return null;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function readBy(string $key, string $value, ?bool $useCache = true)
    {
        $timer_start = microtime(true);

        try {
            switch(strtoupper($key)) {
                case 'ID':
                    if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED') && $useCache)
                        return User::with('roles.permissions', 'profile', 'companies')->find($value);
    
                    return Cache::tags([$value])->remember('readByID'.$value, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.1_HOUR'), function() use ($value) {
                        return User::with('roles.permissions', 'profile', 'companies')->find($value);
                    });
                case 'EMAIL':
                    return User::where('email', '=', $value)->first();
                default:
                    return null;
            }    
        } catch (Exception $e) {
            return null;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(int $id, ?string $name = null, ?array $rolesId = null, ?array $profile = null, ?array $settings = null): ?User
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $usr = User::find($id);

            if (!is_null($name))
                $this->updateUser($usr, $name, false);

            if (!is_null($profile))
                $this->updateProfile($usr, $profile, false);
            
            if (!is_null($rolesId))
                $this->updateRoles($usr, $rolesId, false);
            
            if (!is_null($settings))
                $this->updateSettings($usr, $settings, false);

            DB::commit();

            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateUser(User $user, string $name, bool $useTransactions = true): ?bool
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            //DB::enableQueryLog();

            $retval = $user->update([
                'name' => $name,
            ]);

            //$queryLog = DB::getQueryLog();

            !$useTransactions ? : DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateProfile(User $user, array $profile, bool $useTransactions = true): ?bool
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($profile != null) {
                $pa = $user->profile()->first();

                $retval = $pa->update([
                    'first_name' => array_key_exists('first_name', $profile) ? $profile['first_name']:$pa->first_name,
                    'last_name' => array_key_exists('last_name', $profile) ? $profile['last_name']:$pa->last_name,
                    'address' => array_key_exists('address', $profile) ? $profile['address']:$pa->address,
                    'city' => array_key_exists('city', $profile) ? $profile['city']:$pa->city,
                    'postal_code' => array_key_exists('postal_code', $profile) ? $profile['postal_code']:$pa->postal_code,
                    'country' => array_key_exists('country', $profile) ? $profile['country']:$pa->country,
                    'status' => array_key_exists('status', $profile ) ? $profile['status']:$pa->status,
                    'tax_id' => array_key_exists('tax_id', $profile) ? $profile['tax_id']:$pa->tax_id,
                    'ic_num' => array_key_exists('ic_num', $profile) ? $profile['ic_num']:$pa->ic_num,
                    'img_path' => array_key_exists('img_path', $profile ) ? $profile['img_path']:$pa->img_path,
                    'remarks' => array_key_exists('remarks', $profile) ? $profile['remarks']:$pa->remarks
                ]);
            }

            !$useTransactions ? : DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateRoles(User $user, array $rolesId, bool $useTransactions = true): ?User
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $updated_usr = $user->syncRoles($rolesId);

            !$useTransactions ? : DB::commit();

            return $updated_usr;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateSettings(User $user, array $settings, bool $useTransactions = true): ?bool
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            !$useTransactions ? : DB::commit();

            $retval = 0;
            foreach ($settings as $key => $value) {
                $setting = $user->settings()->where('key', $key)->first();
                if (!$setting || $value == null) continue;
                if ($setting->value != $value) {
                    $retval += $setting->update([
                        'value' => $value,
                    ]);
                }
            }

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function resetPassword(string $email): void
    {
        Password::sendResetLink(['email' => $email], function (Message $message) {
            $message->subject('Reset Password');
        });
    }

    public function resetTokens(int $id): void
    {
        $usr = User::find($id);

        $usr->tokens()->delete();
    }

    public function createDefaultSetting(): array
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
