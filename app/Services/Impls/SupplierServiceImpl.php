<?php

namespace App\Services\Impls;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\SupplierService;
use App\Models\Supplier;

class SupplierServiceImpl implements SupplierService
{
    public function create(
        $company_id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $supplier = new Supplier();
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->term = $term;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->is_tax = $is_tax;
            $supplier->tax_number = $tax_number;
            $supplier->remarks = $remarks;
            $supplier->status = $status;

            $supplier->save();

            DB::commit();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }

    }

    public function read($companyId, $search = '', $paginate = true, $perPage = 10)
    {
        return Supplier::paginate();
    }

    public function update(
        $id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::where('id', '=', $id);

            $supplier->update([
                'code' => $code,
                'name' => $name,
                'term' => $term,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'is_tax' => $is_tax,
                'tax_number' => $tax_number,
                'remarks' => $remarks,
                'status' => $status
            ]);

            DB::commit();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }


    public function delete($id)
    {
        $supplier = Supplier::find($id);

        $retval = $supplier->delete();

        return $retval;
    }

    public function isUniqueCode($code, $userId, $exceptId)
    {
        $usr = User::find($userId);
        $companies = $usr->companies()->pluck('company_id');

        $result = Supplier::whereIn('id', $companies)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
