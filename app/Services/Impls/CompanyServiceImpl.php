<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CompanyService;
use App\Models\User;
use App\Models\Company;

class CompanyServiceImpl implements CompanyService
{
    public function create(
        $code,
        $name,
        $address,
        $default,
        $status,
        $userId
    )
    {
        DB::beginTransaction();

        try {
            $usr = User::find($userId)->first();

            if ($usr->companies()->count() == 0) {
                $default = 1;
                $status = 1;
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
        }
    }

    public function read($userId, $search = '', $paginate = true, $perPage = 10)
    {
        $usr = User::find($userId)->first();
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
    }

    public function getAllActiveCompany($userId)
    {
        $usr = User::find($userId)->first();
        $compIds = $usr->companies()->pluck('company_id');
        return Company::where('status', '=', 1)->whereIn('id',  $compIds)->get();
    }

    public function update(
        $id,
        $code,
        $name,
        $address,
        $default,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $company = Company::where('id', '=', $id);

            $company->update([
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'default' => $default,
                'status' => $status
            ]);

            DB::commit();

            return $company;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete($userId, $id)
    {
        DB::beginTransaction();

        try {
            $company = Company::find($id);

            $usr = User::find($userId);
            $usr->companies()->detach([$company->id]);

            $retval = $company->delete();

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function generateUniqueCode()
    {
        
    }

    public function isUniqueCode($code, $userId, $exceptId)
    {
        $usr = User::find($userId);
        $companies = $usr->companies()->pluck('company_id');

        $result = Company::whereIn('id', $companies)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }

    public function isDefaultCompany($companyId)
    {
        return Company::where('id', '=', $companyId)->first()->default == 1 ? true:false;
    }

    public function resetDefaultCompany($userId)
    {
        DB::beginTransaction();

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
        }
    }

    public function getCompanyById($companyId)
    {
        return Company::find($companyId)->first();
    }

    public function getDefaultCompany($userId)
    {
        $usr = User::find($userId);
        return $usr->companies()->where('default','=', 1)->first();
    }
}
