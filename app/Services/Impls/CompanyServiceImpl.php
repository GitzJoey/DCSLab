<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Actions\RandomGenerator;
use App\Services\CompanyService;

use App\Models\User;
use App\Models\Company;
use finfo;

class CompanyServiceImpl implements CompanyService
{
    public function __construct()
    {
        
    }
    
    public function create(
        string $code, 
        string $name, 
        string $address, 
        int $default, 
        int $status, 
        int $userId
    ): Company
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $usr = User::find($userId);
            if (!$usr) return null;

            if ($usr->companies()->count() == 0) {
                $default = 1;
                $status = 1;
            }

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $generatedCode = '';
                do {
                    $generatedCode = $this->generateUniqueCode();

                } while (!$this->isUniqueCode($generatedCode, $userId));

                $code = $generatedCode;
            }

            $company = new Company();
            $company->code = $code;
            $company->name = $name;
            $company->address = $address;
            $company->default = $default;
            $company->status = $status;

            $company->save();

            $usr->companies()->attach([$company->id]);

            DB::commit();

            return $company;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(int $userId, string $search = '', bool $paginate = true, int $perPage = 10)
    {
        $timer_start = microtime(true);

        try {
            $usr = User::find($userId);
            if (!$usr) return null;
    
            $compIds = $usr->companies()->pluck('company_id');
            
            if (empty($search)) {
                $companies = Company::whereIn('id', $compIds)->latest();
            } else {
                $companies = Company::whereIn('id', $compIds)->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $companies->paginate($perPage);
            } else {
                return $companies->get();
            }
        } catch (Exception $e) {
            return null;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function getAllActiveCompany(int $userId)
    {
        $timer_start = microtime(true);

        try {
            $usr = User::find($userId);
            if (!$usr) return null;
    
            $compIds = $usr->companies()->pluck('company_id');
            return Company::where('status', '=', 1)->whereIn('id',  $compIds)->get();    
        } catch (Exception $e) {
            return null;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(
        int $id, 
        string $code, 
        string $name, 
        string $address, 
        int $default, 
        int $status
    ): Company
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $company = Company::find($id);

            $company->update([
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'default' => $default,
                'status' => $status
            ]);

            DB::commit();

            return $company->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(int $userId, int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $company = Company::find($id);

            if ($company) {
                $usr = User::find($userId);
                $usr->companies()->detach([$company->id]);
    
                $retval = $company->delete();    
            }

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        return $code;
    }

    public function isUniqueCode(string $code, int $userId, ?int $exceptId = null): bool
    {
        $user = User::find($userId);

        if ($user->companies->count() == 0) return true;

        $result = $user->companies()->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }

    public function isDefaultCompany(int $companyId): bool
    {
        return Company::where('id', '=', $companyId)->first()->default == 1 ? true:false;
    }

    public function resetDefaultCompany(int $userId): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $usr = User::find($userId);
            $compIds = $usr->companies()->pluck('company_id');

            $retval = Company::whereIn('id', $compIds)
                      ->update(['default' => 0]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info(__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function getCompanyById(int $companyId): Company
    {
        return Company::find($companyId)->first();
    }

    public function getDefaultCompany(int $userId): Company
    {
        $usr = User::find($userId);
        return $usr->companies()->where('default','=', 1)->first();
    }
}
