<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CompanyService;
use App\Models\Company;

class CompanyServiceImpl implements CompanyService
{
    public function create(
        $code,
        $name,
        $default,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $company = new Company();
            $company->code = $code;
            $company->name = $name;
            $company->default = $default;
            $company->status = $status;

            $company->save();

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
        return Company::where('created_by', '=', $userId)->paginate();
    }

    public function getAllActiveCompany()
    {
        return Company::where('status', '=', 1)->get();
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

    public function getCompanyById($id)
    {
        return Company::find($id);
    }

    public function delete($id)
    {
        $company = Company::find($id);

        return $company->delete();
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

    public function resetDefaultCompany($userId)
    {
        DB::beginTransaction();

        try {

            $retval = Company::where('created_by', '=', $userId)
                      ->update(['default' => 0]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }
}