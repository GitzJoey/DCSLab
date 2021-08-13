<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\BranchService;
use App\Models\Branch;

use Vinkla\Hashids\Facades\Hashids;

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
            $branch->company_id = Hashids::decode($company_id)[0];
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
        return Branch::with('company')->paginate();
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
        return Branch::find($id);
    }

    public function delete($id)
    {
        $branch = Branch::find($id);

        return $branch->delete();
        
    }
}