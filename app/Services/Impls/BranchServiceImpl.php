<?php

namespace App\Services\Impls;

use App\Services\BranchService;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class BranchServiceImpl implements BranchService
{
    public function create(
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $branch = new Branch();
            $branch->company_id = $company_id;
            $branch->code = $code;
            $branch->name = $name;
            $branch->address = $address;
            $branch->city = $city;
            $branch->contact = $contact;
            $branch->remarks = $remarks;
            $branch->status = $status;

            $branch->save();

            DB::commit();

            return $branch->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return Branch::with('company')->bySelectedCompany()->paginate();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $branch = Branch::where('id', '=', $id);

            $retval = $branch->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'remarks' => $remarks,
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
        $branch = Branch::find($id);

        return $branch->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create':
                $count = Branch::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Branch::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}