<?php

namespace App\Services\Impls;

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
        $taxable_enterprice,
        $tax_id,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $supplier = new Supplier();
            $supplier->company_id = $company_id;
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->term = $term;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->taxable_enterprice = $taxable_enterprice;
            $supplier->tax_id = $tax_id;
            $supplier->remarks = $remarks;
            $supplier->status = $status;

            $supplier->save();

            DB::commit();

            return $supplier->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }

    }

    public function read($userId)
    {
        return Supplier::with('company')->bySelectedCompany()->paginate();
    }

    public function getAllSupplier()
    {
        return Supplier::get();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $taxable_enterprice,
        $tax_id,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::where('id', '=', $id);

            $retval = $supplier->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'term' => $term,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'taxable_enterprice' => $taxable_enterprice,
                'tax_id' => $tax_id,
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

    public function delete($id)
    {
        $supplier = Supplier::find($id);

        return $supplier->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = Supplier::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Supplier::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}