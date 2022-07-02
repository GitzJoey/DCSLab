<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
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
use App\Traits\CacheHelper;

class UserServiceImpl implements UserService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function register(array $input): User
    {
        $name = $input['name'];

        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];

            $name = str_replace(' ', '', $name);
        } else {
            $first_name = $name;
            $last_name = '';
        }

        $profile = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'status' => RecordStatus::ACTIVE,
        ];

        $roles = [Role::where('name', UserRoles::USER->value)->first()->id];

        $usr = $this->create(
            $input,
            $roles,
            $profile
        );

        return $usr;
    }

    public function create(array $userArr, array $rolesArr, array $profileArr): User
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            //throw New \Exception('Test Exception From Services');

            $usr = new User();
            $usr->name = $userArr['name'];
            $usr->email = $userArr['email'];

            $password = $userArr['password'];

            if (empty($password)) {
                $usr->password = Hash::make((new RandomGenerator())->generateAlphaNumeric(5));
                $usr->password_changed_at = null;
            } else {
                $usr->password = Hash::make($password);
                $usr->password_changed_at = Carbon::now();
            }

            $usr->save();

            $pa = new Profile();

            $pa->first_name = array_key_exists('first_name', $profileArr) ? $profileArr['first_name']:'';
            $pa->last_name = array_key_exists('last_name', $profileArr) ? $profileArr['last_name']:'';
            $pa->address = array_key_exists('address', $profileArr) ? $profileArr['address']:null;
            $pa->city = array_key_exists('city', $profileArr) ? $profileArr['city']:null;
            $pa->postal_code = array_key_exists('postal_code', $profileArr) ? $profileArr['postal_code']:null;
            $pa->country = array_key_exists('country', $profileArr) ? $profileArr['country']:null;
            $pa->tax_id = array_key_exists('tax_id', $profileArr) ? $profileArr['tax_id']:'';
            $pa->ic_num = array_key_exists('ic_num', $profileArr) ? $profileArr['ic_num']:'';
            $pa->status = array_key_exists('status', $profileArr) ? $profileArr['status']:1;
            $pa->img_path = array_key_exists('img_path', $profileArr) ? $profileArr['img_path']:null;
            $pa->remarks = array_key_exists('remarks', $profileArr) ? $profileArr['remarks']:null;

            $usr->profile()->save($pa);

            $settings = $this->createDefaultSetting();
            $usr->settings()->saveMany($settings);

            $usr->attachRoles($rolesArr);

            if (env('AUTO_VERIFY_EMAIL', true))
                $usr->markEmailAsVerified();

            DB::commit();

            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.' '.'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(string $search = '', bool $paginate = true, int $page = 1, int $perPage = 10, bool $useCache = true): Paginator|Collection
    {
        $timer_start = microtime(true);
        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;
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
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $usr->paginate(abs($perPage));
            } else {
                $result = $usr->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function read(User $user): User
    {
        return $user->with('roles, profile, settings')->first();
    }

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch(strtoupper($key)) {
                case 'ID':
                    return User::with('roles.permissions', 'profile', 'companies.branches', 'settings')->find($value);
                case 'EMAIL':
                    return User::where('email', '=', $value)->first();
                default:
                    return null;
            }    
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(User $user, ?array $userArr = null, ?array $rolesArr = null, ?array $profileArr = null, ?array $settingsArr = null): User
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if (!is_null($userArr))
                $this->updateUser($user, $userArr, false);

            if (!is_null($profileArr))
                $this->updateProfile($user, $profileArr, false);
            
            if (!is_null($rolesArr))
                $this->updateRoles($user, $rolesArr, false);
            
            if (!is_null($settingsArr))
                $this->updateSettings($user, $settingsArr, false);

            DB::commit();

            $this->flushCache();

            return $user->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateUser(User $user, array $userArr, bool $useTransactions = true): bool
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            //DB::enableQueryLog();

            $retval = $user->update([
                'name' => $userArr['name'],
            ]);

            //$queryLog = DB::getQueryLog();

            !$useTransactions ? : DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateProfile(User $user, array $profileArr, bool $useTransactions = true): bool
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($profileArr != null) {
                $pa = $user->profile()->first();

                $retval = $pa->update([
                    'first_name' => array_key_exists('first_name', $profileArr) ? $profileArr['first_name']:$pa->first_name,
                    'last_name' => array_key_exists('last_name', $profileArr) ? $profileArr['last_name']:$pa->last_name,
                    'address' => array_key_exists('address', $profileArr) ? $profileArr['address']:$pa->address,
                    'city' => array_key_exists('city', $profileArr) ? $profileArr['city']:$pa->city,
                    'postal_code' => array_key_exists('postal_code', $profileArr) ? $profileArr['postal_code']:$pa->postal_code,
                    'country' => array_key_exists('country', $profileArr) ? $profileArr['country']:$pa->country,
                    'status' => array_key_exists('status', $profileArr) ? $profileArr['status']:$pa->status,
                    'tax_id' => array_key_exists('tax_id', $profileArr) ? $profileArr['tax_id']:$pa->tax_id,
                    'ic_num' => array_key_exists('ic_num', $profileArr) ? $profileArr['ic_num']:$pa->ic_num,
                    'img_path' => array_key_exists('img_path', $profileArr) ? $profileArr['img_path']:$pa->img_path,
                    'remarks' => array_key_exists('remarks', $profileArr) ? $profileArr['remarks']:$pa->remarks
                ]);
            }

            !$useTransactions ? : DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateRoles(User $user, array $rolesArr, bool $useTransactions = true): User
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $updated_usr = $user->syncRoles($rolesArr);

            !$useTransactions ? : DB::commit();

            return $updated_usr->refresh();
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function updateSettings(User $user, array $settingsArr, bool $useTransactions = true): bool
    {
        !$useTransactions ? : DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $retval = 0;
            foreach ($settingsArr as $key => $value) {
                $setting = $user->settings()->where('key', $key)->first();
                if (!$setting || $value == null) continue;
                if ($setting->value != $value) {
                    $retval += $setting->update([
                        'value' => $value,
                    ]);
                }
            }

            !$useTransactions ? : DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ? : DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function resetPassword(string $email): void
    {
        Password::sendResetLink(['email' => $email], function (Message $message) {
            $message->subject('Reset Password');
        });
    }

    public function resetTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function createDefaultSetting(): array
    {
        return Setting::factory()->createDefaultSetting();
    }
}
