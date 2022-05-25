<?php

namespace App\Services\Impls;

use Exception;
use App\Models\Branch;

use App\Models\Company;
use App\Traits\CacheHelper;
use App\Services\BranchService;
use App\Actions\RandomGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Pagination\Paginator;

class BranchServiceImpl implements BranchService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        bool $is_main, 
        ?string $remarks = null,
        int $status,
    ): ?Branch
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $branch = new Branch();
            $branch->company_id = $company_id;
            $branch->code = $code;
            $branch->name = $name;
            $branch->address = $address;
            $branch->city = $city;
            $branch->contact = $contact;
            $branch->is_main = $is_main;
            $branch->remarks = $remarks;
            $branch->status = $status;

            $branch->save();

            DB::commit();

            $this->flushCache();

            return $branch;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null
    {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            if (!$companyId) return null;

            $branch = Branch::with('company')
                        ->whereCompanyId($companyId);
    
            if (empty($search)) {
                $branch = $branch->latest();
            } else {
                $branch = $branch->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                $result = $branch->paginate($perPage);
            } else {
                $result = $branch->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function getBranchByCompanyId(int $companyId)
    {
        $timer_start = microtime(true);

        try {
            return Branch::where('company_id', '=', $companyId)->where('status', '=', 1)->get();
        } catch (Exception $e) {
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function getMainBranchByCompanyId(int $companyId): Branch
    {
        $timer_start = microtime(true);

        try {
            $branch = Branch::where('company_id', '=', $companyId)->where('is_main', '=', 1)->get()[0];
            return $branch;
        } catch (Exception $e) {
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        bool $is_main,
        ?string $remarks = null,
        int $status,
    ): ?Branch
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $branch = Branch::find($id);
    
            $branch->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'is_main' => $is_main,
                'remarks' => $remarks,
                'status' => $status,
            ]);

            DB::commit();

            $this->flushCache();

            return $branch->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function resetMainBranch(int $companyId): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $company = Company::find($companyId);
            $branchIds = $company->branches()->pluck('id');

            $retval = Branch::whereIn('id', $branchIds)->update(['is_main' => 0]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        return Config::get('const.ERROR_RETURN_VALUE');
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $branch = Branch::find($id);

            if ($branch) {
                $retval = $branch->delete();
            }

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return $retval;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Branch::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}