<?php

namespace App\Services\Impls;

use Exception;
use App\Models\User;

use App\Models\Employee;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Pagination\Paginator;

class EmployeeServiceImpl implements EmployeeService
{
    public function create(
        int $company_id,
        array $user,
        string $join_date,
        int $status
    ): ?Employee
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $userService = app(UserService::class);
            $user_id = $userService->create(
                $user[0]['name'],
                $user[0]['email'],
                $user[0]['password'],
                $user[0]['rolesId'],
                $user[0]['profile']
            );
            $user_id = $user_id->id;
            
            $employee = new Employee();
            $employee->company_id = $company_id;
            $employee->user_id = $user_id;
            $employee->join_date = $join_date;
            $employee->status = $status;

            $employee->save();

            DB::commit();

            $this->flushCache();

            return $employee;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null
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

            if (!$companyId) return null;

            $employee = Employee::with('company')
                        ->whereCompanyId($companyId);
    
            if (empty($search)) {
                $employee = $employee->latest();
            } else {
                $employee = $employee->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                $result = $employee->paginate($perPage);
            } else {
                $result = $employee->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function readFromCache($key)
    {
        try {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED')) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            if (!Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->has($key)) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            return Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' Key: '.$key. ', Tags: ['.auth()->user()->id.', '.class_basename(__CLASS__).']');
        }
    }

    private function saveToCache($key, $val)
    {
        try {
            if (empty($key)) return;

            Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->add($key, $val, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.ENV'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' Key: '.$key. ', Tags: ['.auth()->user()->id.', '.class_basename(__CLASS__).']');
        }
    }

    private function flushCache()
    {
        try {
            Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' Tags: ['.(is_null(auth()->user()) ? '':auth()->id()).', '.class_basename(__CLASS__).']');
        }
    }

    public function update(
        int $id,
        int $company_id,
        int $user_id,
        int $status
    ): ?Employee
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $employee = Employee::find($id);
    
            $employee->update([
                'company_id' => $company_id,
                'user_id' => $user_id,
                'status' => $status,
            ]);

            $user_id = new User;

            DB::commit();

            $this->flushCache();

            return $employee->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $employee = Employee::find($id);

            if ($employee) {
                $retval = $employee->delete();
            }

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Employee::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}