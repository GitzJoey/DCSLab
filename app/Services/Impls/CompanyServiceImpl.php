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
        $status
    )
    {
        DB::beginTransaction();

        try {
            $company = new Company();
            $company->code = $code;
            $company->name = $name;
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

    public function read()
    {
        return Company::paginate();
    }

    public function getAllActiveCompany()
    {
        return Company::where('status', '=', 1)->get();
    }

    public function update(
        $id,
        $code,
        $name,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $company = Company::where('id', '=', $id);

            $retval = $company->update([
                'code' => $code,
                'name' => $name,
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

        
    }
}