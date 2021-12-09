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
        $default,
        $status,
        $userId
    )
    {
        DB::beginTransaction();

        try {
            $user = User::find($userId);
            if ($user->companies()->count() == 0) $default = 1;

            $company = new Company();
            $company->code = $code;
            $company->name = $name;
            $company->default = $default;
            $company->status = $status;

            $company->save();

            $user->companies()->attach([$company->id]);

            DB::commit();

            return $company->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read($userId)
    {
        $usr = User::find($userId);
        $compIds = $usr->companies()->where('user_id', '=', $userId)->pluck('company_id');
        return Company::whereIn('id', $compIds)->paginate();
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
        $default,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $company = Company::where('id', '=', $id);

            $retval = $company->update([
                'code' => $code,
                'name' => $name,
                'default' => $default,
                'status' => $status
            ]);

            DB::commit();

            return $retval;
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

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create':
                $count = Company::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Company::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
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